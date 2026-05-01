// pages/my/activity/meetingCreate/index.js
const app = getApp();
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
const dateTimePicker = require('../../../../utils/dateTimePicker.js');
import WxValidate from "../../../../utils/validate.js";

Page({

  /**
   * 页面的初始数据
   */
  data: {
    detail: {},
    formParams: {
      id: '',
      cover_image: '',
      images: [],
      type: 2,
      activity_time: '',
      apply_end_time: '',
      longitude: '',
      latitude: '',
      address_title: '',
      address_name: '',
      speArr:[],
      agendaArr: [],
      undertakeArr: []
      // content: '',
    },
    dateTimeArray_1: null,
    dateTime_1: null,
    dateTimeArray_2: null,
    dateTime_2: null,
    dateTimeArray_3: null,
    dateTime_3: null,
    current_address_title: '',
    totalNum: 9,
    speList:[],
    agendaList:[],
    undertakeList: [],
    is_submit: false,
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
      that.initValidate();
      that.processDate();
    }, 1500);
  },


  getDetail: function (id) {
    let that = this;
    let _id = id;
    util.post(api.ActivityDetail, {id: _id})
        .then(response => {
          let _data = response.data.data;
          that.setData({
            'formParams.id': _id,
            'formParams.cover_image': _data.cover_image,
            'formParams.activity_time': _data.activity_time,
            'formParams.activity_end_time': _data.activity_end_time,
            'formParams.longitude': _data.longitude,
            'formParams.latitude': _data.latitude,
            'formParams.address_title': _data.address_title,
            'formParams.address_name': _data.address_name,
            'formParams.apply_end_time': _data.apply_end_time,
            'formParams.type': _data.type,
            'formParams.content': _data.content,
            'formParams.images': _data.images,
            'formParams.speArr':_data.speArr,
            'formParams.agendaArr':_data.agendaArr,
            'formParams.undertakeArr': _data.undertakeArr,
            current_address_title: _data.address_title,
            detail: _data,
          });
          wx.setStorageSync('speArr',_data.speArr);
          wx.setStorageSync('agendaArr',_data.agendaArr);
          wx.setStorageSync('undertakeArr', _data.undertakeArr);
          that.onShow();

        });
  },

  UploadImage: function (event) {
    let that = this;
    util.fliesUpload().then((respond) => {
      let uploadResponse = JSON.parse(respond.data);
      console.log(uploadResponse);
      that.setData({
        'formParams.cover_image': uploadResponse.url
      });

    }).catch((err) => {
      console.log(err)
    })
  },

  processDate: function () {
    let _activity_time = this.data.formParams.activity_time;
    let _apply_end_time = this.data.formParams.apply_end_time;
    let _activity_end_time = this.data.formParams.activity_end_time;
    let obj1 = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear, _activity_time);
    let obj2 = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear, _apply_end_time);
    let obj3 = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear, _activity_end_time);
    // 精确到分的处理，将数组的秒去掉
    obj1.dateTimeArray.pop();
    obj1.dateTime.pop();
    obj2.dateTimeArray.pop();
    obj2.dateTime.pop();
    obj3.dateTimeArray.pop();
    obj3.dateTime.pop();
    this.setData({
      dateTime_1: obj1.dateTime,
      dateTimeArray_1: obj1.dateTimeArray,
      dateTime_2: obj2.dateTime,
      dateTimeArray_2: obj2.dateTimeArray,
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
    let _formTime = 'formParams.activity_time';
    if (currentTimeType == 2) {
      _formTime = 'formParams.apply_end_time';
    } else if (currentTimeType == 3) {
      _formTime = 'formParams.activity_end_time';
    }
    let realTime = dateArr[0][currentTime[0]] + '-' + dateArr[1][currentTime[1]] + '-' + dateArr[2][currentTime[2]] + ' ' + dateArr[3][currentTime[3]] + ':' + dateArr[4][currentTime[4]];
    this.setData({[_dateTime]: currentTime, [_formTime]: realTime});
  },

  changeDateTimeColumn(e) {
    let currentTimeType = e.currentTarget.dataset.type;
    let _dateTime = 'dateTime_' + currentTimeType;
    let _dateTimeArray = 'dateTimeArray_' + currentTimeType;
    let _formTime = 'formParams.activity_time';
    if (currentTimeType == 2) {
      _formTime = 'formParams.apply_end_time';
    } else if (currentTimeType == 3) {
      _formTime = 'formParams.activity_end_time';
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
    let longitudeStr = 'formParams.longitude';
    let latitudeStr = 'formParams.latitude';
    let addressTitleStr = 'formParams.address_title';
    let addressInfoStr = 'formParams.address_name';
    wx.chooseLocation({
      success: function (res) {
        console.log(res);
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
      'formParams.address_title': _value
    });
  },


  moreUploadImage: function (event) {
    let that = this;
    let currentImages = that.data.formParams.images;
    let currentNum = currentImages.length;
    let totalNum = that.data.totalNum;
    if (currentNum == totalNum) {
      wx.showToast({
        title: '最多只能上传' + totalNum + '张图片！',
        duration: 1000
      });
      return false;
    }
    let lastNum = totalNum - currentNum;
    util.multipartFliesUpload(lastNum).then((respond) => {
      let uploadUrlData = respond;
      let _imagesData = currentImages.concat(uploadUrlData);
      console.log(uploadUrlData);
      that.setData({
        'formParams.images': _imagesData,
      });
      wx.showToast({
        title: '上传成功',
        duration: 1000
      });
    }).catch((err) => {
      console.log(err)
      wx.showToast({
        title: '上传失败！',
        duration: 1000
      });
    })
  },

  DeleteImage: function (event) {
    console.log('444');
    let that = this;
    let _key = event.currentTarget.dataset.id;
    let _imagesData = that.data.formParams.images;
    _imagesData.splice(_key, 1);
    that.setData({
      'formParams.images': _imagesData,
    });
  },

  formSubmit: function (e) {
    let that = this;
    let _is_submit = that.data.is_submit;
    if (_is_submit) {
      wx.showToast({title: '不要重复提交', icon: 'none', duration: 800});
      return false;
    }
    let _agendaArr = that.data.formParams.agendaArr;
    let _speArr = that.data.formParams.speArr;
    if (_agendaArr.length == 0) {
      wx.showToast({ title: '请至少添加一项议程', icon: 'none', duration: 800 });
      return false;
    }
    if (_speArr.length == 0) {
      wx.showToast({ title: '请至少添加一项规格', icon: 'none', duration: 800 });
      return false;
    }
    let _id = that.data.formParams.id;
    let _url = api.ActivityCreate;
    let success_title = '创建成功';
    if (_id) {
      _url = api.ActivityUpdate;
      success_title = '更新成功';
    }

    setTimeout(function () {
      let _formData = e.detail.value;
      _formData.speArr = _speArr;
      _formData.agendaArr = _agendaArr;
      let postData = Object.assign(_formData, that.data.formParams);
      if (!that.WxValidate.checkForm(postData)) {
        //表单元素验证不通过，此处给出相应提示
        let error = that.WxValidate.errorList[0];
        wx.showToast({title: error.msg, icon: 'none', duration: 800});
        return false;
      }
      that.setData({
        is_submit: true
      });
      util.post(_url, postData)
          .then(response => {
            let _data = response.data.data;
            wx.showToast({title: success_title, icon: 'none', duration: 800});
            setTimeout(function () {
              wx.removeStorageSync('speArr');
              wx.removeStorageSync('agendaArr');
              wx.removeStorageSync('undertakeArr');
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
    that.getSpe();
    that.getAgenda();
    that.getUndertake();
  },

  getSpe: function () {
    let that = this;
    let _speArr = wx.getStorageSync('speArr');
    console.log(_speArr);
    if (_speArr && _speArr.length > 0) {
      util.post(api.SpecificationGetList, {idArr:_speArr})
          .then(response => {
            let _speList = response.data.data;
            if (!_speList || _speList.length === 0) {
              _speList = [];
            }
            that.setData({
              speList: _speList,
              'formParams.speArr': _speArr
            })
          });
    } else {
      that.setData({
        speList: [],
        'formParams.speArr': []
      })
    }

  },

  getAgenda: function () {
    let that = this;
    let _agendaArr = wx.getStorageSync('agendaArr');
    console.log(_agendaArr);
    if (_agendaArr && _agendaArr.length > 0) {
      util.post(api.AgendaGetList, {idArr:_agendaArr})
          .then(response => {
            let _agendaList = response.data.data;
            if (!_agendaList || _agendaList.length === 0) {
              _agendaList = [];
            }
            console.log(_agendaList);
            that.setData({
              agendaList: _agendaList,
              'formParams.agendaArr': _agendaArr
            })
          });
    } else {
      that.setData({
        agendaList: [],
        'formParams.agendaArr': []
      })
    }

  },

  deleteAgenda: function (e) {
    let that = this;
    let _id = e.currentTarget.dataset.id;
    let _agendaArr = that.data.formParams.agendaArr;
    for (let index in _agendaArr) {
      if (_agendaArr[index] == _id) {
        _agendaArr.splice(index, 1);
        wx.setStorageSync('agendaArr', _agendaArr);
        that.getAgenda();
        return false;
      }
    }

  },

  getUndertake: function () {
    let that = this;
    let _undertakeArr = wx.getStorageSync('undertakeArr');
    console.log(_undertakeArr);
    if (_undertakeArr && _undertakeArr.length > 0) {
      util.post(api.UndertakeDataUrl, { idArr: _undertakeArr })
        .then(response => {
          let _List = response.data.data;
          if (!_List || _List.length === 0) {
            _List = [];
          }
          console.log(_List)
          that.setData({
            undertakeList: _List,
            'formParams.undertakeArr': _undertakeArr
          })
        });
    } else {
      that.setData({
        undertakeList: [],
        'formParams.undertakeArr': []
      })
    }

  },

  deleteUndertake: function (e) {
    let that = this;
    let _id = e.currentTarget.dataset.id;
    let _undertakeArr = that.data.formParams.undertakeArr;
    for (let index in _undertakeArr) {
      if (_undertakeArr[index] == _id) {
        _undertakeArr.splice(index, 1);
        wx.setStorageSync('undertakeArr', _undertakeArr);
        that.getUndertake();
        return false;
      }
    }

  },

  deleteSpe: function (e) {
    let that = this;
    let _id = e.currentTarget.dataset.id;
    let _speArr = that.data.formParams.speArr;
    for (let index in _speArr) {
      if (_speArr[index] == _id) {
        _speArr.splice(index, 1);
        wx.setStorageSync('speArr', _speArr);
        that.getSpe();
        return false;
      }
    }
    // wx.showModal({
    //   title: '你确定删除该规格吗？',
    //   content: '删除后需要重新添加',
    //   success: function (res) {
    //     if (res.confirm) {
    //       util.post(api.SpecificationDelete, {id:_id})
    //           .then(response => {
    //             let _speArr = that.data.formParams.speArr;
    //             for (let index in _speArr) {
    //               if (_speArr[index] == _id) {
    //                 _speArr.splice(index, 1);
    //                 wx.setStorageSync('speArr',_speArr);
    //                 that.onShow();
    //                 return false;
    //               }
    //             }
    //           });
    //     }
    //   }
    // })

  },


  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url
    })
  },

  initValidate() {
    let rules = {
      cover_image: {
        required: true,
      },
      title: {
        required: true,
        maxlength: 200
      },
      content: {
        required: true
      },
      activity_time: {
        required: true,
      },
      // apply_end_time: {
      //   required: true,
      // },
      address_title: {
        required: true
      },
    };

    let message = {
      cover_image: {
        required: "请上传封面图片"
      },
      title: {
        required: "请输入标题",
        maxlength: '标题过长'
      },
      content: {
        required: "请输入内容"
      },
      activity_time: {
        required: "请选择会议时间"
      },
      // apply_end_time: {
      //   required: "请选择报名截止时间"
      // },
      address_title: {
        required: "请选择地址"
      },

    };
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
    wx.removeStorageSync('speArr');
    wx.removeStorageSync('agendaArr');
    wx.removeStorageSync('undertakeArr');
  },

})