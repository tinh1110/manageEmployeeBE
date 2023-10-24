<?php

namespace App\Common;

class CommonConst
{
    const UPDATE_METHOD = ['PUT', 'PATCH'];
    const LOCAL_STORAGE = 'public';
    const DIRECTORY_SEPARATOR = '/';

    // Start action const
    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const INDEX = 'index';
    const SHOW = 'show';
    const DETAIL = 'detail';
    const CREATE = 'create';
    const STORE = 'store';
    const UPDATE = 'update';
    const UPDATE_STATUS = 'update_status';
    const REMOVE = 'remove';
    const DELETE = 'delete';
    const COMMENT = 'comment';
    const REJECT = 'reject';
    const CONFIRM = 'confirm';
    // End action const

    // Date, time, etc
    const SECOND = 'second';
    const MINUTE = 'minute';
    const HOUR = 'hour';
    const DAY = 'day';
    // End data, time, etc ,...
    const DEFAULT_PER_PAGE = 10;

    // Order constant
    const ORDER_TYPE_ASC = 'asc';
    const ORDER_TYPE_DESC = 'desc';
    // End order constant

    //role_attendance const
    const CAN_REVIEW = 1;
    const ONLY_VIEW = 2;
    // end role_attendance
    const ROLE_ADMIN = 1;
    //attendance review
    const NOT_REVIEWED = 0;
    const ATTENDANCE_ACCEPT = 1;
    const ATTENDANCE_REJECT = 2;

    //import status
    const PROCESSING = 0;
    const DONE = 1;
    // end attendance review
    const ADMIN_ID = 1;
    //attendance img path
    const ATTENDANCE_IMG_PATH = 'user_attendance';
    //end attendance img path

    //user avatar path
    const USER_AVATAR_PATH = 'user_avatar';
    //event img path
    const EVENT_IMG_PATH = 'event_img';

    //attendance colors
    const ATTENDANCE_ACCEPT_COLOR = 'green';
    const ATTENDANCE_REJECT_COLOR = 'red';
    //end attendance colors

    //attendance import
    const CHUNK_SIZE_ATTENDANCE = 100;
    const BATCH_SIZE_ATTENDANCE = 100;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 2;
    const STATUS_APPROVE = 1;
    const STATUS_DECLINE = 2;
    //end attendance import

}
