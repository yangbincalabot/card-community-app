// pages/discover/apply/addApplicant/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";
Page({

  /**
   * 页面的初始数据
   */
  data: {
    detail:{},
    regionArr:[
      {id: 1,title: '中国大陆籍'},
      {id: 2,title: '其他国家或地区'}
    ],
    region_title:'中国大陆籍',
    region_type: 1,
    endDate:'',
    date:'',
    uid:'',
    id:'',
    sex_hidden: true,
    is_submit: false
  },

  bindRegionChange: function (e) {
    let that = this;
    let _key = e.detail.value;
    let currentRegion = that.data.regionArr[_key];
    let _sex_hidden = false;
    if (currentRegion.id == 1) {
      _sex_hidden = true;
    }
    that.setData({
      region_type: currentRegion.id,
      region_title: currentRegion.title,
      sex_hidden: _sex_hidden
    })
  },

  initValidate() {
    let rules = {
      name: {
        required: true,
        maxlength: 100
      },
      phone: {
        required: true,
        tel:true
      }
    }

    let message = {
      name: {
        required: '请输入姓名',
        maxlength: '姓名过长'
      },
      phone: {
        required: "请输入手机号"
      }
    }
    //实例化当前的验证规则和提示消息
    this.WxValidate = new WxValidate(rules, message);
  },

  getEndDate: function () {
    let that = this;
    let _endDate = util.formatTime(new Date());
    that.setData({
      endDate: _endDate
    })
  },


  formSubmit(e) {
    let that = this;
    let param = e.detail.value;
    let _region_type = that.data.region_type;
    let _date = that.data.date;
    let _identity_number = param.identity_number;
    param['region_type'] = _region_type;
    if (_region_type == 2) {
      if (!param.sex) {
        wx.showToast({ title: '请选择性别', icon: 'none', duration: 800 })
        return false;
      }
      if (!_date) {
        wx.showToast({ title: '请选择出生日期', icon: 'none', duration: 800 })
        return false;
      }
      param['birthday'] = _date;
    } else {
      if (!_identity_number) {
        wx.showToast({ title: '请输入身份证号码', icon: 'none', duration: 800 })
        return false;
      }
      let cardBool = that.checkIdCard(_identity_number);
      if (!cardBool) {
        wx.showToast({ title: '请正确输入身份证号码', icon: 'none', duration: 800 })
        return false;
      }
      let _sex = 2;
      if (parseInt(_identity_number.substr(16, 1)) % 2 == 1) {
        _sex = 1;
      }
      param['sex'] = _sex;
    }
    if (!that.WxValidate.checkForm(param)) {
      let error = that.WxValidate.errorList[0];
      wx.showToast({ title: error.msg, icon: 'none', duration: 800 })
      return false;
    }
    
    that.realSubmit(param);
  },

  realSubmit:function (postData) {
    let that = this;
    let _is_submit = that.data.is_submit;
    if (_is_submit) {
      wx.showToast({ title: '不要重复提交', icon: 'none', duration: 300 });
      return false;
    }
    let _url = api.ApplicantCreate;
    let _id = that.data.id;
    let _uid = that.data.uid;
    if (_id) {
      _url = api.ApplicantUpdate;
      postData['id'] = _id;
      postData['uid'] = _uid;
    }
    that.setData({
      is_submit:true
    })
    util.post(_url, postData)
      .then(response => {
        let _data = response.data.data;
        wx.showToast({ title: '操作成功', icon: 'none', duration: 800 });
        setTimeout(function(){
          wx.navigateBack({
            delta: 1
          })
        },600);
        
      });
    setTimeout(function(){
      that.setData({
        is_submit: false
      })
    },3000);
  },

  bindDateChange: function (e) {
    let that = this;
    let _date = e.detail.value;
    that.setData({
      date: _date
    })
  },

  checkIdCard: function (value) {
    let that = this;
    return /^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/.test(value);
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
    that.initValidate();
    that.getEndDate();
  },

  getDetail(id) {
    let that = this;
    let _id = id;
    util.post(api.ApplicantShow, { id: _id})
      .then(response => {
        let _data = response.data.data;
        console.log(_data)
        let region_title = '中国大陆籍';
        let _sex_hidden = true;
        if (_data.region_type == 2) {
          region_title = '其他国家或地区';
          _sex_hidden = false;
        }
        that.setData({
          detail: _data,
          id: _id,
          uid: _data.user_id,
          region_type: _data.region_type,
          region_title: region_title,
          sex_hidden: _sex_hidden,
          sex_detail: _data.sex,
          date: _data.birthday
        })
      });
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