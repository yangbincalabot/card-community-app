// pages/my/activity/create/index.js
const app = getApp();
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
const dateTimePicker = require('../../../../utils/dateTimePicker.js');
import WxValidate from "../../../../utils/validate.js";

const html = require('../../../../utils/htmlParse/parser.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        ResourceRootUrl: api.ResourceRootUrl,
        speArr: [1,2,3,4,5,6],
        default_type_title: '',
        current_address_title: '',
        formSelectParams: {
            id: '',
            cover_image: '',
            type: 1,
            activity_time: '',
            apply_end_time: '',
            longitude: '',
            latitude: '',
            address_title: '',
            address_name: '',
            // content: '',
        },
        dateTimeArray_1: null,
        dateTime_1: null,
        dateTimeArray_3: null,
        dateTime_3: null,
        ActivityAll: {},
        is_submit: false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        let _id = options.id;
        if (_id) {
            that.getDetail(_id);
        }
        setTimeout(function () {
            that.processDate();
            that.initValidate();
        }, 1500);
    },


    getDetail: function (id) {
        let that = this;
        let _id = id;
        util.post(api.ActivityDetail, {id: _id})
            .then(response => {
                let _data = response.data.data;
                let _content = that.getContent(_data.content);
                that.setData({
                    'formSelectParams.id': _id,
                    'formSelectParams.uid': _data.user_id,
                    'formSelectParams.cover_image': _data.cover_image,
                    'formSelectParams.activity_time': _data.activity_time,
                    'formSelectParams.longitude': _data.longitude,
                    'formSelectParams.latitude': _data.latitude,
                    'formSelectParams.address_title': _data.address_title,
                    'formSelectParams.address_name': _data.address_name,
                    'formSelectParams.apply_end_time': _data.apply_end_time,
                    'formSelectParams.type': _data.type,
                    'formSelectParams.content': _data.content,
                    current_address_title: _data.address_title,
                    ActivityAll: _data,
                })
                that.get_type_title(_data.type);
            });
    },


    UploadImage: function (event) {
        let that = this;
        util.fliesUpload().then((respond) => {
            let uploadResponse = JSON.parse(respond.data);
            console.log(uploadResponse);
            that.setData({
                'formSelectParams.cover_image': api.ResourceRootUrl + uploadResponse.relative_url
            });

        }).catch((err) => {
            console.log(err)
        })
    },

    processDate: function () {
        let _activity_time = this.data.formSelectParams.activity_time;
        let _apply_end_time = this.data.formSelectParams.apply_end_time;
        let obj1 = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear, _activity_time);
        let obj3 = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear, _apply_end_time);
        // 精确到分的处理，将数组的秒去掉
        obj1.dateTimeArray.pop();
        obj1.dateTime.pop();
        obj3.dateTimeArray.pop();
        obj3.dateTime.pop();
        this.setData({
            dateTime_1: obj1.dateTime,
            dateTimeArray_1: obj1.dateTimeArray,
            dateTime_3: obj3.dateTime,
            dateTimeArray_3: obj3.dateTimeArray
        });
    },

    changeDateTime(e) {
        let currentTime = e.detail.value;
        let currentTimeType = e.currentTarget.dataset.type;
        let _dateTime = 'dateTime_' + currentTimeType;
        let _dateTimeArray = 'dateTimeArray_' + currentTimeType;
        let dateArr = this.data[_dateTimeArray];
        let _formTime = 'formSelectParams.activity_time';
        if (currentTimeType == 3) {
            _formTime = 'formSelectParams.apply_end_time';
        }
        let realTime = dateArr[0][currentTime[0]] + '-' + dateArr[1][currentTime[1]] + '-' + dateArr[2][currentTime[2]] + ' ' + dateArr[3][currentTime[3]] + ':' + dateArr[4][currentTime[4]];
        this.setData({[_dateTime]: currentTime, [_formTime]: realTime});
    },

    changeDateTimeColumn(e) {
        let currentTimeType = e.currentTarget.dataset.type;
        let _dateTime = 'dateTime_' + currentTimeType;
        let _dateTimeArray = 'dateTimeArray_' + currentTimeType;
        let _formTime = 'formSelectParams.activity_time';
        if (currentTimeType == 3) {
            _formTime = 'formSelectParams.apply_end_time';
        }

        let arr = this.data[_dateTime], dateArr = this.data[_dateTimeArray];
        arr[e.detail.column] = e.detail.value;
        dateArr[2] = dateTimePicker.getMonthDay(dateArr[0][arr[0]], dateArr[1][arr[1]]);
        this.setData({
            [_dateTimeArray]: dateArr,
            [_dateTime]: arr
        });
    },

    /**
     * 地理位置
     * @param e
     */
    getChooseLocation: function (e) {
        let that = this;
        wx.getSetting({
            success(res) {
                if (!res.authSetting['scope.userLocation']) {
                    wx.authorize({
                        scope: 'scope.userLocation',
                        success(res) {
                            that.setChooseLocation()
                        },
                        fail(res) {
                            console.log(res);
                            wx.showModal({
                                title: '是否授权当前位置',
                                content: '需要获取您的地理位置，请确认授权，否则地图功能将无法使用',
                                success: function (res) {
                                    if (res.confirm) {
                                        that.isOpenSetting();
                                    }
                                }
                            })
                        }
                    })
                } else {
                    that.setChooseLocation()
                }
            },
            fail(res) {
                that.isOpenSetting();
            }
        })

    },

    isOpenSetting: function () {
        let that = this;
        wx.openSetting({
            success: (res) => {
                if (res.authSetting['scope.userLocation']) {
                    wx.removeStorageSync('userLocationStatus');
                    that.setChooseLocation();
                }
            }
        })
    },


    setChooseLocation: function () {
        let that = this;
        let longitudeStr = 'formSelectParams.longitude';
        let latitudeStr = 'formSelectParams.latitude';
        let addressTitleStr = 'formSelectParams.address_title';
        let addressInfoStr = 'formSelectParams.address_name';
        wx.chooseLocation({
            success: function (res) {
                console.log(res)
                that.setData({
                    [longitudeStr]: res.longitude,
                    [latitudeStr]: res.latitude,
                    [addressTitleStr]: res.address,
                    [addressInfoStr]: res.name,
                    current_address_title: res.address,
                });
            },
            fail: function (res) {
                console.log(res)
            }
        });
    },

    changeAddressTitle(e) {
      let that = this;
      let _value = e.detail.value;
      that.setData({
        'formSelectParams.address_title': _value
      })
      console.log(that.data.formSelectParams.address_title)
    },

    setContent: function () {
        let that = this;
        let _content = wx.getStorageSync('activity_content');
        wx.removeStorageSync('activity_content');
        if (_content) {
            let _new_content = that.getContent(_content);
            html.default.definedCustomTag({figure: 'div', figcaption: ''});
            let _nodes = html.default.getRichTextJson(_new_content);
            let _newContent = _nodes.children;
            that.setData({
                contentNode: _newContent,
                'formSelectParams.content': _content
            })
        }
    },

    getContent: function (content) {
        let that = this;
        let html = content
            .replace(/&nbsp;/g, '\xa0\xa0')
            .replace(/\/>/g, '>')
            .replace(/<img([\s\w"-=\/\.:;]+)((?:(height="[^"]+")))/ig, '<img$1')
            .replace(/<img([\s\w"-=\/\.:;]+)((?:(width="[^"]+")))/ig, '<img$1')
            .replace(/<img([\s\w"-=\/\.:;]+)((?:(style="[^"]+")))/ig, '<img$1')
            .replace(/<img([\s\w"-=\/\.:;]+)((?:(alt="[^"]+")))/ig, '<img$1')
            .replace(/<img([\s\w"-=\/\.:;]+)/ig, '<img$1 style="max-width: 100%;margin:0 auto; height:auto; border-radius: 8Px;"');
        return html;
    },

    // 页面跳转
    navigateToContent: function (event) {
        let that = this;
        let url = event.currentTarget.dataset.url;
        let content = that.data.formSelectParams.content;
        wx.setStorageSync('activity_content', content);
        setTimeout(function () {
            wx.navigateTo({
                url: '../content/index'
            });
        }, 200);
    },


    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        let that = this;
        that.setContent();
    },

  




    formSubmit: function (e) {
        let that = this;
        let _is_submit = that.data.is_submit;
        if (_is_submit) {
            wx.showToast({title: '不要重复提交', icon: 'none', duration: 300});
            return false;
        }
        let _speArr = that.data.speArr;
        // if (_speArr.length == 0) {
        //   wx.showToast({ title: '请至少选择一项规格', icon: 'none', duration: 300 });
        //   return false;
        // }
        let _id = that.data.formSelectParams.id;
        let _url = api.ActivityCreate;
        let success_title = '创建成功';
        if (_id) {
            _url = api.ActivityUpdate;
            success_title = '更新成功';
        }
      
    
        setTimeout(function () {
          let _formData = e.detail.value;
          _formData.speArr = _speArr;
          let postData = Object.assign(_formData, that.data.formSelectParams);
          console.log(postData)
            // if (!that.WxValidate.checkForm(postData)) {
            //     //表单元素验证不通过，此处给出相应提示
            //     let error = that.WxValidate.errorList[0];
            //     console.log(error);
            //     wx.showToast({title: error.msg, icon: 'none', duration: 800})
            //     return false;
            // }
            that.setData({
                is_submit: true
            })
            util.post(_url, postData)
                .then(response => {
                    let _data = response.data.data;
                    wx.showToast({title: success_title, icon: 'none', duration: 800});
                    setTimeout(function () {
                        wx.navigateBack({
                            delta: 1
                        })
                    }, 700);
                });
        }, 100);
        setTimeout(function () {
            that.setData({
                is_submit: false
            })
        }, 3000);
    },


    initValidate() {
        let rules = {
            cover_image: {
                required: true
            },
            type: {
                required: true
            },
            title: {
                required: true,
                maxlength: 200
            },
            activity_time: {
                required: true
            },
            apply_end_time: {
                required: true
            },
            content: {
                required: true
            }
        }

        let message = {
            cover_image: {
                required: '请上传活动图片'
            },
            type: {
                required: "请选择分类"
            },
            title: {
                required: "请输入标题",
                maxlength: '标题过长'
            },
            activity_time: {
                required: "请选择活动时间"
            },
            apply_end_time: {
                required: "请选择报名截止时间"
            },
            content: {
                required: "请输入内容"
            }
        }
        //实例化当前的验证规则和提示消息
        this.WxValidate = new WxValidate(rules, message);
    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function () {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },


})