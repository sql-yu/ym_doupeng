@extends('layouts.app')

@section('title',$title)
@section('keywords',$keywords)
@section('description',$description)

@section('content')
<link rel="stylesheet" href="/css/index.css" class="{!! $check_code !!}">

    <div class="main-container {!! $check_code !!}">
        <div class="wrapper {!! $check_code !!}">
            @if(isset($cate['game_cate_name']))<div class="nav-details {!! $check_code !!}">{{$cate['game_cate_name']??""}}</div>@endif
            <div class="box {!! $check_code !!}">



{{--                跨两列两行-大图 --}}
{{--                  <a class="box-item row-col-1" href="/jogo/{{$game1['uuid_code']}}">--}}
{{--                      <img class="box-img loading-img" data-original="{{$game1['image']}}" alt="{{$game1['game_name']}}"/>--}}
{{--                  </a>--}}

{{--                 <a class="box-item row-col-1" href="/jogo/{{$game2['uuid_code']}}">--}}
{{--                      <img class="box-img loading-img" data-original="{{$game2['image']}}" alt="{{$game2['game_name']}}"/>--}}
{{--                  </a>--}}

{{--                <!-- 跨两列-导航 -->--}}
{{--                @foreach($cates as $key=> $cate)--}}
{{--                    @if($key<8)--}}
{{--                <a class="box-item row-{{$key+1}}" href="/tag/{{$cate['id']}}">--}}
{{--                    <div class="content-nav-left">--}}
{{--                        <img class='loading-img' data-original="{{$cate['cate_image']}}" alt="{{$cate['game_cate_name']}}">--}}
{{--                    </div>--}}
{{--                    <div class="content-nav-right">{{$cate['game_cate_name']}}</div>--}}
{{--                </a>--}}
{{--                @endif--}}
{{--                @endforeach--}}


{{--                 普通数据列表 --}}
                @foreach($games as $game)
                    <a class="box-item row-col-{!! $check_code !!} {!! $check_code !!}" href="/{!! $app_game_page !!}/{{$game['uuid_code']}}-{{$game['uuid_2_code']}}{{$game['id']}}" data-check="{{$check_data_code}}">
                        <img class="box-img loading-img {!! $check_code !!}" data-original="{{$game['image']}}" alt="{{$game['game_name']}}"/>
                    </a>
{{--                    <a class="box-item" href="/jogo/{{$game['uuid_code']}}">--}}
{{--                        <img width="120px" height="120px" class="box-img loading-img"  data-original="{{$game['image']}}" alt="{{$game['game_name']}}" />--}}
{{--                    </a>--}}
                @endforeach

            </div>

{{--           描述--}}
{{--            <div class="container-description">--}}
{{--                <div class="desc-title">{{$setting->desc_title}}</div>--}}
{{--                <div class="content-desc-wrap">--}}
{{--                    <div class="content-desc" style="white-space: pre-line;">{!! $setting->content_desc!!}</div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>

@endsection

