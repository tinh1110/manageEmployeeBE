Xin chào, <b>{{ $user->name }}</b>
<p>
    Chúc <b>{{ $user->name }}</b> một ngày tốt lành!
</p>
<p>
    BO kính gửi tới toàn thể CBNV Thông báo về  <b>{{ $event->name }}</b> , cụ thể:
</p>
<p>
    Thời gian: từ <b>{{ $event->start_time}}  đến {{ $event->end_time}} </b>
</p>
<p>
    Mô tả: {{ $event->description }}
</p>
<p>
    Địa điểm: {{ $event->location }}
</p>
<p>
    Chi tiết xem tại trang web công ty.
</p>
<p>
        Mọi thắc mắc xin vui lòng liên hệ bộ phận BO để được giải đáp.
</p>
<i>
    Trân trọng cảm ơn!
</i>


