<?php

namespace App\Repositories;

use App\Models\User;
use App\Helpers\FileHelper;

class UserRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return User::class;
    }

    public function search($query, $column, $data)
    {
        return match ($column) {
            'email', 'gender', 'status' => $query->where($column, $data),
            // search field will search by name or email
            'search', => $query->where(function ($q) use ($data) {
                $q->where('name', 'like', '%'.$data.'%')->orwhere('email', 'like', '%'.$data.'%');
            }),
            'role', => $query->where('role_id', $data),
            'team_id'=> $query->whereHas('team',function($query)use($data) {
                $query->where('id',$data);
            }),
            default => $query,
        };
    }

    // force delete an user by id
    public function forceDelete($id)
    {
        $result = $this->model::withTrashed()->where('id', $id)->first();
        if ($result) {
            $path = $result->avatar;
            if ($path) {
                FileHelper::deleteFileFromStorage($path);
            }
            $result->forceDelete();

            return true;
        }

        return false;
    }

    // force delete multi users
    public function forceDeleteMulti(array $ids = []){
        return $this->model::withTrashed()->whereIn('id', $ids)->forceDelete();
    }

    // restore an user
    public function restore(string $id){
        $user = $this->model::withTrashed()->findOrFail($id);
        if($user){
            $user->restore();
            return true;
        }
  
        return false;
    }

    // restore multi users
    public function restoreMulti(array $ids = []){
        return $this->model::withTrashed()->whereIn('id', $ids)->restore();
    }

    // get user's email for attendance
    public function getEmailsOfUsers(array $ids = []){
        $manager_emails = $this->model::whereIn('id', $ids)->pluck('email');
        return $manager_emails;
    }
}
