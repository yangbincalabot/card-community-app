// pages/discover/apply/index/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
const html = require('../../../../utils/htmlParse/parser.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    apply_group:[],
    new_group:[],
    activityDetail: {},
    total_people: 0,
    total_price: 0,
    postData:{
      'urgent_contact': '',
      'phone':''
    },
    is_hidden: true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    that.setData({
      id: _id
    })
    that.getActivityDetail(_id);
    that.getPostData();
    that.getActivityText();
  },

  getActivityText: function () {
    let that = this;
    util.post(api.ActivityGetText, {})
      .then(response => {
        let _data = response.data;
        html.default.definedCustomTag({ figure: 'div', figcaption: '' });
        _data = _data.replace(/&nbsp;/g, '\xa0\xa0');
        let _nodes = html.default.getRichTextJson(_data);
        let _newContent = _nodes.children;
        that.setData({
          contentNode: _newContent
        })
      });
  },


  getApplyGroup: function (id) {
    let that = this;
    let _apply_group = wx.getStorageSync('activity_apply_group');
    let group = that.data.activityDetail.group;
    let _new_group = [];
    new Promise(function (resolve) {
      resolve();
    }).then(() => {
      for (let index in group) {
        let item = group[index];
        if (_apply_group.indexOf(item.id) != -1) {
          _new_group.push(item);
        }
      }
      }).then(() => {
        that.setData({
          is_hidden: false,
          new_group: _new_group,
          apply_group: _apply_group
        })
        if (_new_group && _new_group.length > 0) {
          that.getChooseApplicant(_new_group);
        }
      });
  },

  getActivityDetail(id) {
    let that = this;
    let _id = id;
    util.post(api.ActivityBigDetail, { id, _id })
      .then(response => {
        let _data = response.data.data;
        console.log(_data)
        if (_data.apply_condition.status != 1) {
          wx.showToast({ title: _data.apply_condition.msg, icon: 'none', duration: 800 });
          setTimeout(function(){
            wx.redirectTo({
              url: '/pages/discover/index/index'
            })
          },700);
          return false;
        }
        that.setData({
          activityDetail: _data
        })
        that.getApplyGroup(_id);
      });
  },

  removeGroup: function (e) {
    let that = this;
    let _key = e.currentTarget.dataset.index;
    let _apply_group = that.data.apply_group;
    let _new_group = that.data.new_group;
    let _remove_id = _new_group[_key].id;
    for (let index in _apply_group) {
      let iv = _apply_group[index];
      if (iv == _remove_id) {
        _apply_group.splice(index, 1);
        break;
      }
    }
    _new_group.splice(_key, 1);
    wx.setStorageSync('activity_apply_group', _apply_group);
    that.setData({
      apply_group: _apply_group,
      new_group: _new_group
    })
  },

  removeChoose: function (e) {
    let that = this;
    let _new_group = that.data.new_group;
    let _choose_applicant = that.data.choose_applicant;
    let _key = e.currentTarget.dataset.key;
    let _choose_id = e.currentTarget.dataset.choose_id;
    let _group_id = e.currentTarget.dataset.group_id;
    let _group_key = e.currentTarget.dataset.group_key;
    let _sex = e.currentTarget.dataset.sex;
    if (_sex == 1) {
      let _sex_num = _new_group[_group_key].man_num;
      _sex_num--;
      _new_group[_group_key].man_num = _sex_num;
    } else {
      let _sex_num = _new_group[_group_key].woman_num;
      _sex_num--;
      _new_group[_group_key].woman_num = _sex_num;
    }
    let _total_people = that.data.total_people;
    let _fee = that.data.activityDetail.fee;
    let _total_price = 0;
    _total_people--;
    if (_fee > 0) {
      _total_price = _total_people * _fee;
    }
    _choose_applicant[_group_id].splice(_key,1);
    _new_group[_group_key].choose.splice(_key, 1);
    wx.setStorageSync('choose_applicant', _choose_applicant);
    that.setData({
      choose_applicant: _choose_applicant,
      new_group: _new_group,
      total_people: _total_people,
      total_price: _total_price
    })
    // console.log(e);
    // console.log(_choose_applicant);
    // console.log(_new_group);
  },

  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url
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
    let that = this;
    let _id = that.data.id;
    that.getApplyGroup(_id);
  },


  getChooseApplicant: function (_new_group) {
    let that = this;
    let _choose_applicant = wx.getStorageSync('choose_applicant');
    let param = {
      'choose_applicant': _choose_applicant,
      'new_group': _new_group
    }
    util.post(api.ApplyProcess, param)
      .then(response => {
        let _data = response.data.data;
        let _total_people = response.data.total_people;
        let _fee = that.data.activityDetail.fee;
        let _total_price = 0;
        if (_fee > 0) {
          _total_price = _total_people * _fee;
        }
        if (_data && _data.length > 0) {
          that.setData({
            choose_applicant: _choose_applicant,
            new_group: _data,
            total_people: _total_people,
            total_price: _total_price
          })
        }
      });
  },

  changeContact: function (e) {
    let that = this;
    let _contact = e.detail.value;
    that.setData({
      'postData.urgent_contact': _contact
    })
  },

  changePhone: function (e) {
    let that = this;
    let _phone = e.detail.value;
    that.setData({
      'postData.phone': _phone
    })
  },

  getPostData: function () {
    let that = this;
    let _postData = wx.getStorageSync('apply_information');
    let _urgent_contact = '';
    let _phone = '';
    if (!_postData) {
      return  false;
    }
    if (_postData.urgent_contact) {
      _urgent_contact = _postData.urgent_contact;
    }
    if (_postData.phone) {
      _phone = _postData.phone;
    }
    that.setData({
      'postData.urgent_contact': _urgent_contact,
      'postData.phone': _phone
    })
  },

  nextStep: function (e) {
    let that = this;
    let _postData = that.data.postData;
    let _phone = _postData.phone;
    let _urgent_contact = _postData.urgent_contact;
    let _total_people = that.data.total_people;
    if (_total_people == 0) {
      wx.showToast({ title: '请至少选择一个报名人', icon: 'none', duration: 800 });
      return false;
    }
    if (!_urgent_contact) {
      wx.showToast({ title: '请输入紧急联系人', icon: 'none', duration: 800 });
      return false;
    }
    if (!_phone) {
      wx.showToast({ title: '请输入手机号', icon: 'none', duration: 800 });
      return false;
    }
    if (!/^1[34578]\d{9}$/.test(_phone)) {
      wx.showToast({ title: '请正确输入手机号', icon: 'none', duration: 800 });
      return false;
    }
    wx.setStorageSync('apply_information', _postData);
    let _activity_id = that.data.id;
    wx.navigateTo({
      url: '../confirm/index?activity_id=' + _activity_id
    })
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