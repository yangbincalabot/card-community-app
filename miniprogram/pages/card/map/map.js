//获取应用实例
const app = getApp()
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');


Page({
    data: {
        latitude: 22.55994, // 当前纬度
        longitude: 114.0539, // 当前经度

        user_latitude: 22.55994, // 用户初始纬度
        user_longitude: 114.0539, // 用户初始经度
        markers: [],
        cell: {}, // 当前选中的名片
        height: '',
        width: '',
        keyword: '',
        circles: [],

        radius: 180, // 默认半径
        showRadius: '',
        loading: true,
        scale: 16,
        show: false,
        list: [], // 列表
        showDetail: false,

    },
    onReady: function (e) {
        this.mapCtx = wx.createMapContext('myMap');

        // util.getRadius(this.mapCtx).then(radius => {
        //     this.setData({
        //         radius: radius,
        //         "circles[0].radius": radius,
        //         showRadius: radius >= 1000 ? (Math.round(radius / 100) / 10).toFixed(2) + "公里" : radius.toFixed(2) + "米"
        //     })
        // })
    },

    onLoad: function () {
        wx.getSystemInfo({
            success: res => {
                this.setData({
                    height: res.windowHeight,
                    width: res.windowWidth
                })
            }
        }),

        util.getNewLocation(true).then(res => {
            this.init(res)
        }).catch(err => {
            this.setData({
                loading: false
            })
            let errMsg = err.errMsg
            if (errMsg.indexOf('auth') !== -1) {
                util.scopeLocation(this.init)
            }
        })
    },



    navigateTo(e) {
        this.url = e.currentTarget.dataset.url
        wx.navigateTo({
            url: this.url,
        })
    },

    init: function (data) {
        // console.log(data)
        let [latitude, longitude] = [Number(data.latitude), Number(data.longitude)]
        this.setData({
            latitude: latitude,
            longitude: longitude,
            user_latitude: latitude,
            user_longitude: longitude
        })
      //  this.setRedius(latitude, longitude)
        this.getCollection();
    },

    // setRedius: function (latitude, longitude) {
    //     let radius = this.data.radius * 2
    //     let circle = {
    //         latitude,
    //         longitude,
    //         color: '#B3D0F870',
    //         fillColor: '#E7ECF188',
    //         radius: radius,
    //         strokeWidth: 1
    //     }
    //     this.setData({
    //         circles: [circle],
    //         loading: false
    //     })

    // },

    bindRegionChange: function (event) {
        // console.log(event)
        let causedBy = event.causedBy
        if (causedBy === 'drag') {
            // 获取地图中心坐标
            this.mapCtx.getCenterLocation({
                success: res => {
                    if (res.latitude && res.longitude) {
                        this.mapCtx.translateMarker({
                            markerId: 0,//所要操作的标记ID，在data中已预先定义
                            autoRotate: false,
                            rotate: 0,
                            duration: 100,
                            destination: {//新的坐标值
                                latitude: res.latitude,
                                longitude: res.longitude,
                            },
                            animationEnd() {
                                // console.log('animation end')
                            }
                        })
                        this.setData({
                            latitude: res.latitude,
                            longitude: res.longitude,
                            'circles[0].latitude': res.latitude,
                            'circles[0].longitude': res.longitude,
                        })
                    }
                }
            })
        }

        // 计算半径
        // util.getRadius(this.mapCtx).then(radius => {
        //     //console.log(radius)
        //     if (this.data.radius != radius) {
        //         this.setData({
        //             radius: radius,
        //             "circles[0].radius": radius,
        //             showRadius: radius >= 1000 ? (Math.round(radius / 100) / 10).toFixed(2) + "公里" : radius.toFixed(2) + "米"
        //         })
        //         //this.getMarkers()
        //     }
        // })
    },

    // 跳转到初始位置
    myPosition: function () {
        this.mapCtx.translateMarker({
            markerId: 0,//所要操作的标记ID，在data中已预先定义
            autoRotate: false,
            rotate: 0,
            duration: 100,
            destination: {//新的坐标值
                latitude: this.data.user_latitude,
                longitude: this.data.user_longitude,
            },
            animationEnd() {
                // console.log('animation end')
            }
        })
        this.setData({
            latitude: this.data.user_latitude,
            longitude: this.data.user_longitude,
            'circles[0].latitude': this.data.user_latitude,
            'circles[0].longitude': this.data.user_longitude,
        })
    },

    phoneCall: function (event) {
        let phone = event.currentTarget.dataset.phone
        wx.makePhoneCall({
            phoneNumber: String(phone)
        })
    },

    getCollection: function(){
        let markers = [
            {
                id: 0,
                latitude: this.data.latitude,
                longitude: this.data.longitude,
                // iconPath: '/images/my_address.png',
                // width: '36px',
                // height: '36px'
            }
        ];
        util.get(api.CardCollectionListUrl).then(res => {
            let list = res.data.data.list;
            let carte;
            for (let elem of list.values()) {
                carte = elem.carte;
                if (carte) {
                    markers.push({
                        id: carte.id,
                        latitude: carte.latitude,
                        longitude: carte.longitude,
                        // title: carte.name,
                        data: elem,
                        iconPath: carte.avatar,
                        width: '36px',
                        height: '36px'
                    })
                }
            }
            this.setData({
                markers: markers,
                list: list
            })
        })
    },
    showDetail: function (event) {
        let markerId = event.markerId
        if (!markerId) {
            return
        }
        let currentData = this.data.markers.find(function (item) {
            return item.id === markerId
        })
        let current = currentData.data;
        current.created_at = current.created_at ? current.created_at.substr(0, 10) : '';
        console.log('current:', current);
        this.setData({
            cell: current,
            showDetail: true
        })


        // let distance = util.getFlatternDistance(this.data.longitude, this.data.latitude, current.location.coordinates[0], current.location.coordinates[1]).toFixed(2)
        // distance = distance < 1 ? distance * 1000 + "米" : distance + "公里"
        // current.distance = distance
        // let phone_list = []
        // let phone = current.phone
        // try {
        //     phone = phone.replace(/ |，|,/, "\n")
        //     phone_list = phone ? phone.split("\n") : []

        // } catch (e) {
        //     phone_list = [phone]

        // }
        // current.phone_list = phone_list

        // this.setData({
        //     current: current,
        //     showDetail: true
        // })
    },

    playPhone: function (e) {
        let that = this;
        let _phone = e.currentTarget.dataset.phone;
        if (!_phone) {
            wx.showToast({ title: '该用户未设置设置电话', icon: 'none', duration: 800 });
            return false;
        }
        wx.makePhoneCall({
            phoneNumber: _phone
        })
    },

    addPhone: function (e) {
        let _item = e.currentTarget.dataset.item;
        console.log(_item)
        let _post = {};
        if (_item.name) {
            _post.firstName = _item.name;
        }
        if (_item.phone) {
            _post.mobilePhoneNumber = _item.phone;
        }
        if (_item.company_name) {
            _post.organization = _item.company_name;
        }
        if (_item.position) {
            _post.title = _item.position;
        }
        if (_item.email) {
            _post.email = _item.email;
        }
        console.log(_post)
        // 添加到手机通讯录
        wx.addPhoneContact(_post)
    },

    // 页面跳转
    navigateToUrl: function (event) {
        let url = event.currentTarget.dataset.url;
        if (url && url !== '#') {
            wx.navigateTo({
                url: url
            });
        }
    },
    onTapBind: function (event) {
        this.setData({
            showDetail: false
        })
    },

})