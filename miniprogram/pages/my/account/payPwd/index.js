const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from '../../../../utils/validate.js'
Page({

    /**
     * 页面的初始数据
     */
    data: {
        sms_switch: 2, // 1-开启短信， 2-关闭短信，后台获取
        formData: {
            sms_code: '',
            cash_password: '',
            cash_password_confirmation: '',
            _method : 'PUT',
        },
        WxValidate: {},
        userInfo: {},
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.initValidate();
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
        wx.showLoading({
            title: '加载中',
            mask: true
        });
        this.getSmsSwitch();
        this.getUserInfo();
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

    getSmsSwitch: function(){
        util.get(api.GetSmsSwitchUrl).then(response => {
            this.setData({
                sms_switch: parseInt(response.data.data.smsSwitch)
            });
        })
    },

    /**
     * 表单-验证字段
     */
    initValidate: function() {

        /**
         * 4-2(配置规则)
         */
        const rules = {

            cash_password: {
                required: true,
                digits: true,
                length: 6,
            },
            cash_password_confirmation: {
                required: true,
                digits: true,
                length: 6,
                equalTo: 'cash_password',
            },

        };
        // 验证字段的提示信息，若不传则调用默认的信息
        const messages = {

            cash_password: {
                required: '请输入新密码',
                digits: '新密码必须为数字',
                length: '新密码为6个字符',


            },
            cash_password_confirmation: {
                required: '请输入确认密码',
                digits: '确认密码必须为数字',
                length: '确认密码为6个字符',
                equalTo: '两次密码输入不一致',
            },
        };
        // 创建实例对象

        this.WxValidate = new WxValidate(rules, messages);
    },
    formSubmit: function (event) {
        this.setData({
            'formData.cash_password': event.detail.value.cash_password,
            'formData.cash_password_confirmation': event.detail.value.cash_password_confirmation,
            'formData.sms_code': event.detail.value.sms_code,
        });


        // 验证表单
        if (!this.WxValidate.checkForm(this.data.formData)) {
            let error = this.WxValidate.errorList[0];
            wx.showToast({
                title: error.msg,
                icon:'none',
                duration: 2000,
            });
            return false
        }

        if(this.data.sms_switch === 1 && this.data.formData.sms_code.length === 0){
            wx.showToast({
                title: '请输入验证码',
                icon:'none',
                duration: 2000,
            });
            return false
        }

        wx.showLoading({
            title: '修改中',
        });

    // 添加到后台
    util.post(api.SetCashPasswordUrl, this.data.formData).then(res => {
      wx.hideLoading();
      wx.showToast({
        title: '修改成功',
        icon: 'success',
        duration: 2000,
        success: function () {
          let back_rul = wx.getStorageSync('apply_pay_back');
            let society_back = wx.getStorageSync('society_pay_back');
            if (back_rul || society_back) {
                try{
                    wx.removeStorageSync('apply_pay_back');
                    wx.removeStorageSync('society_pay_back');
                }catch(e){
                    console.log(e);
                }
                setTimeout(() => {
                wx.navigateBack({
                    delta: 1
                })
                }, 1500)
                return false;
          }
          setTimeout(() => {
            // wx.redirectTo({
            //   url: '../index'
            // })
            wx.navigateBack({
              delta: 1
            })
          }, 1500)
        }
      });
    });
  },
  

    getUserInfo: function () {
        util.get(api.UserIndexUrl).then((res) => {
            wx.hideLoading();
            this.setData({
                userInfo: res.data.data
            });
            // 判断手机号码
            let phone = res.data.data.phone;
            if(!phone|| phone.length === 0){
                wx.showToast({
                    title: '请先绑定手机号',
                    icon: 'none',
                    duration: 1500
                });
                setTimeout(() => {
                    wx.navigateTo({
                        url:'../../../passport/getPhone/index'
                    });
                }, 1500);
                return;
            }
        })
    },
    sendMsg: function(){
        wx.showLoading({
            title: '发送中',
        });
        util.post(api.SendSmsCode, {phone: this.data.userInfo.phone}).then(res => {
            wx.hideLoading();
            let response = res.data.data;
            let msg = response.msg;
            let icon = 'none';
            if(response.status === 1){
                icon = 'success'
            }
            wx.showToast({
                title: msg,
                icon: icon,
                duration: 2000
            });
        })
    }

})