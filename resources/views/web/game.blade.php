@extends('layouts.app')

@section('title',$title)
@section('keywords',$keywords)
@section('description',$description)


@section('content')
<link rel="stylesheet" href="/css/game.css" class="{!! $check_code !!}">


<div class="main-container {!! $check_code !!}">
    <div class="wrapper {!! $check_code !!}">
{{--         广告区列表 --}}
        <div class="box {!! $check_code !!}">
{{--            要播放的预览图 --}}
            <a class="box-item {!! $check_code !!}" href="/{!! $app_detail_page !!}/{{$gameInfo['uuid_code']}}-{{$gameInfo['uuid_2_code']}}{{$gameInfo['id']}}" onclick="sendInfo()" data-check="{{$check_data_code}}">
                <img class="box-img loading-img {!! $check_code !!}" data-original="{{$gameInfo['image']}}" alt="{{$gameInfo['game_name']}}" data-check="{{$check_data_code}}"/>
                <div class="play-btn {!! $check_code !!}">
                    <span class="icon-play {!! $check_code !!}"></span>
                </div>
            </a>

            @foreach($games as $game)
{{--             普通数据列表 --}}
                    <a class="box-item row-col-{!! $check_code !!} {!! $check_code !!}" href="/{!! $app_game_page !!}/{{$game['uuid_code']}}-{{$game['uuid_2_code']}}{{$game['id']}}"  onclick="{!! $check_code !!}()" data-check="{{$check_data_code}}">
                        <img class="box-img loading-img {!! $check_code !!}" data-original="{{$game['image']}}" alt="{{$game['game_name']}}"/>
                    </a>
            @endforeach


{{--             跨两列-导航 --}}
{{--            @foreach($cates as $key=> $cate)--}}
{{--                @if($key<8)--}}
{{--                    <a class="box-item row-{{$key+1}}"  href="/tag/{{$cate['id']}}">--}}
{{--                        <div class="content-nav-left">--}}
{{--                            <img class="loading-img-tag" data-original="{{$cate['cate_image']}}" alt="{{$cate['game_cate_name']}}" />--}}
{{--                        </div>--}}
{{--                        <div class="content-nav-right">{{$cate['game_cate_name']}}</div>--}}
{{--                    </a>--}}
{{--                    @endif--}}
{{--            @endforeach--}}


{{--             广告插入 --}}
{{--            <div class="box-item ads-container-1" style="display:inline-block;width:100%;">--}}
{{--                <p class="my-ads-tag">Advertisement</p>--}}

{{--            </div>--}}

{{--            <!-- 视频列表 -->--}}
{{--            <div class="box-item m-video-list">--}}
{{--                <div class="video-content">--}}
{{--                    <div class="video-list-scroll">--}}
{{--                        @foreach($videos as $video)--}}
{{--                            <a class="video-content-item" href="{{$video['url']}}">--}}
{{--                                <img src="{{$video['image']}}" alt="{{$video['id']}}">--}}
{{--                                <div class="video-player-btn">--}}
{{--                                    <svg height="100%" version="1.1" viewBox="0 0 68 48" width="100%">--}}
{{--                                        <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="rgba(0,0,0,0.8)" class="ytp-large-play-button-bg"></path>--}}
{{--                                        <path d="M 45,24 27,14 27,34" fill="#fff"></path>--}}
{{--                                    </svg>--}}
{{--                                </div>--}}
{{--                            </a>--}}
{{--                        @endforeach--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <!-- 广告插入 -->--}}
{{--            <div class="box-item ads-container-2" style="display:inline-block;width:100%;">--}}
{{--                <p class="my-ads-tag">Advertisement</p>--}}
{{--            </div>--}}
{{--            <!-- 评论 -->--}}
{{--            <div class="box-item reviews">--}}
{{--                <div class="container-description">--}}
{{--                    <div class="desc-title">Instruction</div>--}}
{{--                    <div class="content-desc-wrap">--}}
{{--                        <div class="reviews-obj">--}}
{{--                            <img class="loading-img-tag" data-original="{{$gameInfo['image']}}" alt="{{$gameInfo['game_name']}}"/>--}}
{{--                            <span>{{$gameInfo['game_name']}}</span>--}}
{{--                        </div>--}}
{{--                        <div class="content-desc">{{$gameInfo['instruction']}}</div>--}}
{{--                        <div class="text-right">{{$gameInfo['game_reviews_end']}}</div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <!-- 描述 -->--}}
         

         
        </div>

                <div class="container-description {!! $check_code !!}">
                    <div class="desc-title  {!! $check_code !!}">introdução do jogo</div>
                    <div class="content-desc-wrap {!! $check_code !!}">
                        <div class="content-desc {!! $check_code !!}">{{$gameInfo['description_contents']}}</div>
                    </div>
                </div>
    </div>
</div>
<script>
function {!! $check_code !!}(){
    @if($gameInfo['kwai_id']  && $gameInfo['game_send_purchase'])
        kwaiq.instance('{!!$gameInfo['kwai_id']!!}').track('purchase');
        kwaiq.instance('{!!$gameInfo['kwai_id']!!}').track('addToCart');
    @endif


    @if($gameInfo['tt_id']  && $gameInfo['game_send_purchase'])
        ttq.track('AddToCart');
        ttq.track('CompletePayment');
    @endif
    
}





</script>
@endsection
