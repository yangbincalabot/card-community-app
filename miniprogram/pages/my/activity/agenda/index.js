// pages/my/activity/agenda/index.js
import WxValidate from "../../../../utils/validate";
const dateTimePicker = require('../../../../utils/timePicker.js');
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    formParams: {
      id: '',
      start_time: '',
      end_time: '',
      presenter_id: '',
      presenter: ''
    },
    detail:{},
    dateTimeArray_1: null,
    dateTime_1: null,
    dateTimeArray_2: null,
    dateTime_2: null,
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
    }, 1000);
  },

  getDetail: function (_id) {
    let that = this;
    util.post(api.AgendaDetail, {id:_id}).then(response => {
          let _data = response.data.data;
          that.setData({
            'formParams.id': _data.id,
            'formParams.start_time': _data.start_time,
            'formParams.end_time': _data.end_time,
            'formParams.presenter_id': _data.pid,
            'formParams.presenter': _data.presenter,
            detail: _data,
          })
    });
  },

  deleteAgenda: function () {
    let that = this;
    let _id = that.data.formParams.id;
    if (!_id) {
      wx.showToast({title: '操作失败', icon: 'none', duration: 1000});
      return false;
    }
    wx.showModal({
      title: '你确定删除该议程吗？',
      content: '删除后需要重新添加',
      success: function (res) {
        if (res.confirm) {
          util.post(api.AgendaDelete, {id:_id})
              .then(response => {
                wx.showToast({title: '已删除', icon: 'none', duration: 1000});
                let _agendaArr = wx.getStorageSync('agendaArr');
                if (_agendaArr && _agendaArr.length > 0) {
                  for (let index in _agendaArr) {
                    if (_agendaArr[index] == _id) {
                      _agendaArr.splice(index, 1);
                      wx.setStorageSync('agendaArr',_agendaArr);
                      setTimeout(function () {
                        wx.navigateBack({
                          delta:1
                        });
                      }, 500);
                      return false;
                    }
                  }
                }
              });
        }
      }
    })

  },

  formSubmit: function (e) {
    let that = this;
    let _formData = e.detail.value;
    let postData = Object.assign(_formData, that.data.formParams);
    if (!that.WxValidate.checkForm(postData)) {
      //表单元素验证不通过，此处给出相应提示
      let error = that.WxValidate.errorList[0];
      wx.showToast({title: error.msg, icon: 'none', duration: 800});
      return false;
    }
    let _id = that.data.formParams.id;
    let _url = api.AgendaAdd;
    let success_title = '创建成功';
    if (_id) {
      _url = api.AgendaEdit;
      success_title = '更新成功';
    }

    util.post(_url, postData)
        .then(response => {
          wx.showToast({title: success_title, icon: 'none', duration: 1000});
          let _data = response.data.data;
          if (!_id) {
            let _agendaArr = wx.getStorageSync('agendaArr');
            if (!_agendaArr || _agendaArr === 'undefind') {
              _agendaArr = [];
            }
            if (_data.id) {
              _agendaArr.push(_data.id);
            }
            wx.setStorageSync('agendaArr', _agendaArr);
          }
          setTimeout(function () {
            wx.navigateBack({
              delta:1
            });
          }, 500)
        });
  },

  initValidate() {
    let rules = {
      start_time: {
        required: true
      },
      end_time: {
        required: true
      },
      presenter: {
        required: true,
        maxlength: 30
      },
      title: {
        required: true,
        maxlength: 200
      }
    };

    let message = {
      start_time: {
        required: "请选择议程开始时间"
      },
      end_time: {
        required: "请选择议程结束时间"
      },
      presenter: {
        required: "请输入主讲人",
        maxlength: "主讲人长度过长"
      },
      title: {
        required: "请输入议题",
        maxlength: '议题过长'
      }
    };
    //实例化当前的验证规则和提示消息
    this.WxValidate = new WxValidate(rules, message);
  },

  changeStartTime: function (e) {
    let that = this;
    let _value = e.detail.value;
    that.setData({
      'formParams.start_time': _value
    });
  },

  changeEndTime: function (e) {
    let that = this;
    let _value = e.detail.value;
    that.setData({
      'formParams.end_time': _value
    });
  },

  processDate: function () {
    let _start_time = this.data.formParams.start_time;
    let _end_time = this.data.formParams.end_time;
    let obj1 = dateTimePicker.dateTimePicker(_start_time);
    let obj2 = dateTimePicker.dateTimePicker(_end_time);
    console.log(obj1)
    this.setData({
      dateTime_1: obj1.dateTime,
      dateTimeArray_1: obj1.dateTimeArray,
      dateTime_2: obj2.dateTime,
      dateTimeArray_2: obj2.dateTimeArray
    });
  },
  
  changeDateTime(e) {
    let currentTime = e.detail.value;
    let currentTimeType = e.currentTarget.dataset.type;
    let _dateTime = 'dateTime_' + currentTimeType;
    let _dateTimeArray = 'dateTimeArray_' + currentTimeType;
    let dateArr = this.data[_dateTimeArray];
    let _formTime = 'formParams.start_time';
    if (currentTimeType == 2) {
      _formTime = 'formParams.end_time';
    }
    let realTime = dateArr[0][currentTime[0]] + ':' + dateArr[1][currentTime[1]];
    this.setData({[_dateTime]: currentTime, [_formTime]: realTime});
  },
  
  changeDateTimeColumn(e) {
    let currentTimeType = e.currentTarget.dataset.type;
    let _dateTime = 'dateTime_' + currentTimeType;
    let _dateTimeArray = 'dateTimeArray_' + currentTimeType;
    let _formTime = 'formParams.start_time';
    if (currentTimeType == 2) {
      _formTime = 'formParams.end_time';
    }
    let arr = this.data[_dateTime], dateArr = this.data[_dateTimeArray];
    arr[e.detail.column] = e.detail.value;
    this.setData({
      [_dateTimeArray]: dateArr,
      [_dateTime]: arr
    });
  },



  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url
    })
  },

  // 获取主讲人信息
  getPresenter(_presenter_id) {
    console.log(_presenter_id)
    util.post(api.GetCarteNewsUrl, { id: _presenter_id })
      .then(response => {
        let data = response.data.data;
        this.setData({
          'formParams.presenter_id': _presenter_id,
          'formParams.presenter': data.name
        })
      })
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
    let _presenter_id = wx.getStorageSync('presenter_id');
    if (_presenter_id) {
      wx.removeStorageSync('presenter_id');
      this.getPresenter(_presenter_id);
    }
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




})