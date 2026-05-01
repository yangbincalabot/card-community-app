const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (query) {
      // scene=user_id@129|type@1|id@11
      // 参数以 | 分割，参数名与参数值以 @ 分割
      // user_id:绑定的用户id；type:分享类型 1.活动，2.商品；id:关联id
      console.log('query',query);
      // 默认跳转到首页
      let default_url = '/pages/index/index';
      if(query){
          wx.showLoading({
            title: '加载中...',
            mask: true
          });
          let user_id = 0, type = 0, id = 0;
          let scene = decodeURIComponent(query.scene);
          let params = scene.split('|');
          console.log(params);
          for(let i = 0; i < params.length; i++){
              let param = params[i].split('@');
              switch (param[0]) {
                  case 'user_id':
                      user_id = parseInt(param[1]);
                      break;
                  case 'type':
                      type = parseInt(param[1]);
                      break;
                  case 'id':
                      id = parseInt(param[1]);
                      break
              }
          }
          console.log(user_id);
          if(user_id > 0){
              // 解决未登录的用户在授权登陆后不走关系绑定的问题
              wx.setStorageSync("USER_SCENE", user_id);
              util.post(api.UserRelationStoreUrl, {
                  from_user_id: user_id
              });
          }
          console.log(type);
          wx.hideLoading();
          // 如果有第二个参数，处理跳转，1代表活动，2代表商品
          if(type > 0 && id > 0){
              if(type === 1){
                  default_url = '/pages/discover/detail/index?is_share=1&id=' + id
              }else if(type === 2){
                  default_url = '/pages/product/detail/index?is_share=1&id=' + id
              }

              console.log(default_url);
              wx.redirectTo({
                  url: default_url
              });
              return;
          }
          console.log(default_url);
          wx.redirectTo({
            url: default_url
          });
          return;
      }
      wx.redirectTo({
          url: default_url
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