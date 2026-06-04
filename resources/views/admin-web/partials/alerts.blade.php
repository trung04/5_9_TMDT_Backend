@if(session('status'))
    <div class="notice success">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="notice error">
        <strong>Dữ liệu gửi lên chưa hợp lệ.</strong>
        <ul style="margin: 10px 0 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
