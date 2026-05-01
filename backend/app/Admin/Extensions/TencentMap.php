<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/5
 * Time: 10:40
 */

namespace App\Admin\Extensions;

use Encore\Admin\Form\Field;

// 扩展腾讯地图
class TencentMap extends Field
{
    protected $view = 'admin.tools.tencent-map';
    /**
     * Column name.
     *
     * @var array
     */
    protected $column = [];

    /**
     * Get assets required by this field.
     *
     * @return array
     */
    public static function getAssets()
    {
        switch (config('admin.map_provider')) {
            case 'tencent':
                $js = '//map.qq.com/api/js?v=2.exp&key='.env('TENCENT_MAP_API_KEY');
                break;
            case 'google':
                $js = '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key='.env('GOOGLE_API_KEY');
                break;
            case 'yandex':
                $js = '//api-maps.yandex.ru/2.1/?lang=ru_RU';
                break;
            default:
                $js = '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key='.env('GOOGLE_API_KEY');
        }

        return compact('js');
    }

    public function __construct($column, $arguments)
    {
        $this->column['lat'] = (string) $column;
        $this->column['lng'] = (string) $arguments[0];

        array_shift($arguments);

        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($this->column);

        /*
         * Google map is blocked in mainland China
         * people in China can use Tencent map instead(;
         */
        switch (config('admin.map_provider')) {
            case 'tencent':
                $this->useTencentMap();
                break;
            case 'google':
                $this->useGoogleMap();
                break;
            case 'yandex':
                $this->useYandexMap();
                break;
            default:
                $this->useGoogleMap();
        }
    }

    public function useGoogleMap()
    {
        $this->script = <<<EOT
        (function() {
            function initGoogleMap(name) {
                var lat = $('#{$this->id['lat']}');
                var lng = $('#{$this->id['lng']}');
    
                var LatLng = new google.maps.LatLng(lat.val(), lng.val());
    
                var options = {
                    zoom: 13,
                    center: LatLng,
                    panControl: false,
                    zoomControl: true,
                    scaleControl: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
    
                var container = document.getElementById("map_"+name);
                var map = new google.maps.Map(container, options);
    
                var marker = new google.maps.Marker({
                    position: LatLng,
                    map: map,
                    title: 'Drag Me!',
                    draggable: true
                });
    
                google.maps.event.addListener(marker, 'dragend', function (event) {
                    lat.val(event.latLng.lat());
                    lng.val(event.latLng.lng());
                });
            }
    
            initGoogleMap('{$this->id['lat']}{$this->id['lng']}');
        })();
EOT;
    }

    public function useTencentMap()
    {
        $key = env('TENCENT_MAP_API_KEY');
        $this->script = <<<EOT
        (function() {
            function initTencentMap(name) {
                var lat = $('#{$this->id['lat']}');
                var lng = $('#{$this->id['lng']}');
    
                var center = new qq.maps.LatLng(lat.val(), lng.val());
    
                var container = document.getElementById("map_"+name);
                var map = new qq.maps.Map(container, {
                    center: center,
                    zoom: 13
                });
    
                var marker = new qq.maps.Marker({
                    position: center,
                    draggable: true,
                    map: map
                });
    
                if( ! lat.val() || ! lng.val()) {
                    var citylocation = new qq.maps.CityService({
                        complete : function(result){
                            map.setCenter(result.detail.latLng);
                            marker.setPosition(result.detail.latLng);
                        }
                    });
    
                    citylocation.searchLocalCity();
                }
    
                qq.maps.event.addListener(map, 'click', function(event) {
                    marker.setPosition(event.latLng);
                });
    
                qq.maps.event.addListener(marker, 'position_changed', function(event) {
                    var position = marker.getPosition();
                    lat.val(position.getLat());
                    lng.val(position.getLng());
                });
            }
    
            initTencentMap('{$this->id['lat']}{$this->id['lng']}');
            
            
            $('#search_btn').click(function(){
                let city = $("select[name='city'] option:selected").text();
                if(!city || city === '—— 市 ——'){
                    swal('请选择城市名', '', 'error');
                    return;
                }
                
                console.log(city);
            
                let address = $('#address').val();
                if(address.length <= 0 ){
                    swal('请输入地址', '', 'error');
                    return;
                }
                
                
                let data = {
                        boundary: 'region('+ city +',0)',
                        page_size: 15,
                        page_index: 1,
                        keyword: address,
                        orderby: '_distance',
                        key: '{$key}',
                        output: 'jsonp'
                    };
                $.ajax({
                    url: 'https://apis.map.qq.com/ws/place/v1/search',
                    type: 'GET',
                    dataType: 'jsonp',
                    data: data,
                    jsonp:"callback",
                    jsonpCallback:"QQmap",
                    success: function(res){
                        let lists = res.data;
                        let list_length = lists.length;
                        let map_list = '';
                        if(list_length > 0){
                            for(let i = 0; i < list_length; i++){
                                map_list += '<li style="cursor:pointer" data-latitude="'+ lists[i].location.lat +'" data-longitude="'+ lists[i].location.lng +'"   data-address="'+ lists[i].address +'" class="list-group-item click-map">'+ lists[i].title +'  (地址: '+ lists[i].address +')</li>'
                            }
                            $('#map_list').html(map_list);
                            $('#myModal').modal();
                        }
                    }
                })
                
                
                
            })
            
            
            // 针对全国搜索
            $('#search_btn_nation').click(function(){            
                let address = $('#address').val();
                if(address.length <= 0 ){
                    swal('请输入地址', '', 'error');
                    return;
                }
                
                
                let data = {
                        boundary: 'region(全国)',
                        page_size: 15,
                        page_index: 1,
                        keyword: address,
                        orderby: '_distance',
                        key: '{$key}',
                        output: 'jsonp'
                    };
                $.ajax({
                    url: 'https://apis.map.qq.com/ws/place/v1/search',
                    type: 'GET',
                    dataType: 'jsonp',
                    data: data,
                    jsonp:"callback",
                    jsonpCallback:"QQmap",
                    success: function(res){
                        let lists = res.data;
                        let list_length = lists.length;
                        let map_list = '';
                        if(list_length > 0){
                            for(let i = 0; i < list_length; i++){
                                map_list += '<li style="cursor:pointer" data-latitude="'+ lists[i].location.lat +'" data-longitude="'+ lists[i].location.lng +'"  data-address="'+ lists[i].address +'" class="list-group-item click-map">'+ lists[i].title +'  (地址: '+ lists[i].address +')</li>'
                            }
                            $('#map_list').html(map_list);
                            $('#myModal').modal();
                        }
                    }
                })
                
                
                
            })
            
            $('#myModal').on('click','.click-map', function(){
                let latitude = $(this).data('latitude');
                let longitude = $(this).data('longitude');
                let address = $(this).data('address');
                let btn_type = $(this).data('btn_type');
                $('#{$this->id['lat']}').val(latitude);
                $('#{$this->id['lng']}').val(longitude);
                $('#address').val(address);
                $('#myModal').modal('hide')
                initTencentMap('{$this->id['lat']}{$this->id['lng']}');
            })
        })();
EOT;
    }

    public function useYandexMap()
    {
        $this->script = <<<EOT
        (function() {
            function initYandexMap(name) {
                ymaps.ready(function(){
        
                    var lat = $('#{$this->id['lat']}');
                    var lng = $('#{$this->id['lng']}');
        
                    var myMap = new ymaps.Map("map_"+name, {
                        center: [lat.val(), lng.val()],
                        zoom: 18
                    }); 
    
                    var myPlacemark = new ymaps.Placemark([lat.val(), lng.val()], {
                    }, {
                        preset: 'islands#redDotIcon',
                        draggable: true
                    });
    
                    myPlacemark.events.add(['dragend'], function (e) {
                        lat.val(myPlacemark.geometry.getCoordinates()[0]);
                        lng.val(myPlacemark.geometry.getCoordinates()[1]);
                    });                
    
                    myMap.geoObjects.add(myPlacemark);
                });
    
            }
            
            initYandexMap('{$this->id['lat']}{$this->id['lng']}');
        })();
EOT;
    }
}