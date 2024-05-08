<div class="container-header {!! $check_code !!}">
    <div class="header {!! $check_code !!}">
    </div>

    <div class="m-nav-container {!! $check_code !!}" id="mContainer">
        <div class="m-nav-mask {!! $check_code !!}" id="mask" onclick="closeFrame()"></div>
        <div class="m-nav-box {!! $check_code !!}">
            <div class="m-header {!! $check_code !!}">
                <div class="icon-close {!! $check_code !!}" onclick="closeFrame()"></div>
                <div class="m-title single-line {!! $check_code !!}" onclick="closeFrame(3)">Baby Game</div>
            </div>
            <div class="m-nav-list {!! $check_code !!}">
                @foreach($cates as $key=> $cat)
                 @if($key <8)
                <a href="/tag/{{$cat['id']}}" class="m-nav-item">
                    <div class="m-nav-img">
                        <img src="{{$cat['cate_image']}}" alt="{{$cat['game_cate_name']}}" />
                    </div>
                   <div class="m-nav-text">{{$cat['game_cate_name']}}</div>
                </a>
                @endif
                @endforeach
            </div>

        </div>
    </div>
</div>

