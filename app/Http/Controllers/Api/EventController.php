<?php

namespace App\Http\Controllers\Api;

use App\Common\CommonConst;
use App\Helpers\FileHelper;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\Event\EventResource;
use App\Http\Resources\Event\EventTypeResource;
use App\Jobs\SendMailForUsers;
use App\Models\User;
use App\Repositories\EventRepository;
use App\Repositories\EventTypeRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventController extends BaseApiController
{

    public function __construct(protected EventRepository $eventRepository,protected EventTypeRepository $eventTypeRepository)
    {
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $condition = $request->all();

        $events = $this->eventRepository->getByCondition($condition, ['created_by']);
        $result = EventResource::collection($events);

        return $this->sendPaginationResponse($events, $result);
    }

    public function delete($id): \Illuminate\Http\JsonResponse
    {
        $path = $this->eventRepository->findOrFail($id)->image;
        if ($path) {
            FileHelper::deleteFileFromStorage($path);
        }
        $event = $this->eventRepository->delete($id);
        if ($event) {
            return $this->sendResponse(null, __('common.deleted'));
        }
        return $this->sendError(__('common.not_found'), null, Response::HTTP_NOT_FOUND);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeEvent(): \Illuminate\Http\JsonResponse
    {
        $types = $this->eventTypeRepository->all();
        $result = EventTypeResource::collection($types);
        return $this->sendResponse($result, __('common.get_data_success'));
    }

    /**
     * Store an user to db
     *
     * @param  CreateEventRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateEventRequest $request): \Illuminate\Http\JsonResponse
    {
        // Get data valid from request
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $imgName = [];
            foreach ($request->image as $img) {
                $imgPath =pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME).'-'.  time().'.'.$img->extension();
                $folder = CommonConst::EVENT_IMG_PATH;
                FileHelper::saveFileToStorage($folder, $img, $imgPath);
                $imgName[] = $folder.'/'.$imgPath;
            }
            $data['image'] = $imgName;

        } else {
            $data['image'] =[];
        }
        $data['created_by_id'] = auth()->user()->id;
        $event = $this->eventRepository->create($data);
        if ($request->sendMail =="true") {
            $users = User::all();
            $data1 = [
                'event' => $event,
                'users' => $users,
            ];
            dispatch(new SendMailForUsers($data1));
        }
        $result = EventResource::make($event);
        return $this->sendResponse($result);
    }

    /**
     * Get event info by id before update
     *
     * @param  string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(string $id): \Illuminate\Http\JsonResponse
    {
        $event = $this->eventRepository->findOrFail($id, ['created_by']);
        $result = EventResource::make($event);

        return $this->sendResponse($result);
    }

    /**
     * Update event
     *
     * @param  UpdateEventRequest  $request
     * @param  string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEventRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $paths = $this->eventRepository->findOrFail($id)->image ?? [];
        $delete = $request->input('delete') ?? [];

        foreach ($delete as $del) {
            FileHelper::deleteFileFromStorage($del);
        }
        $temp = array_diff($paths,$delete);


        if ($request->hasFile('image')) {
            foreach ($request->image as $img) {
                $imgPath =pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME).'-'.  time().'.'.$img->extension();
                $folder = CommonConst::EVENT_IMG_PATH;

                FileHelper::saveFileToStorage($folder, $img, $imgPath);
                $temp[] = $folder.'/'.$imgPath;
            }
        }

        $data['image'] = $temp;
        $data['updated_by_id'] = auth()->user()->id;
        $event = $this->eventRepository->update($id, $data);
        if ($request->sendMail=="true") {
            $users = User::all();
            $data1 = [
                'event' => $event,
                'users' => $users,
            ];
            dispatch(new SendMailForUsers($data1));
        }
        $result = EventResource::make($event);
        return $this->sendResponse($result);
    }
}
