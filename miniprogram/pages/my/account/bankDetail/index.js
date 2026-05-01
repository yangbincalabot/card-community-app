const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from '../../../../utils/validate.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
    bank_id: '',
    userBank: {},
    select_bank_name: '',
    formData: {
      bank_id: '',
      card_name: '',
      card_number: '',
      _method: 'PUT',
      id: '',
    },
    WxValidate: {},
    banks: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getBanks();
    this.data.bank_id = options.id;
    this.initValidate();
    this.getBankInfo();
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



  navigatorToUrl:function (e) {
    let _url = e.currentTarget.dataset.url;
    if(_url){
      wx.navigateTo({
        url: _url
      })
    }
  },
  getBankInfo: function () {
    util.get(api.BankDetailUrl, {
      id: this.data.bank_id
    }).then(res => {
      let result = res.data.data;
      this.setData({
          userBank: result,
          'formData.bank_id': result.bank.id,
          'formData.card_name': result.card_name,
          'formData.card_number': result.card_number,
          'formData.id': result.id,
          select_bank_name: result.bank.name,
      })
    })
  },

  deleteBank: function () {
    wx.showModal({
      title: '提示',
      content: '确定删除此银行卡？',
      success: (res) => {
        if (res.confirm) {
          wx.showLoading({
            title: '删除中',
            mask: true,
            success: () => {
              util.post(api.BankDeleteUrl, {
                id: this.data.bank_id,
                _method: 'DELETE',
              }).then(res => {
                wx.hideLoading();
                wx.showToast({
                  title: '删除成功',
                  duration: 2000
                });
                setTimeout(() => {
                  wx.redirectTo({
                    url: '../bank/index'
                  })
                }, 2000)
              })
            }
          });
        }
      }
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

      card_name: {
        required: true,
      },
      bank_id: {
        required: true,
      },
      card_number: {
        required: true,
        digits: true,
        rangelength: [16, 19]
      }

    };
    // 验证字段的提示信息，若不传则调用默认的信息
    const messages = {

      card_name: {
        required: '请输入持卡人姓名',
      },
      bank_id: {
        required: '请选择银行',
      },
      card_number: {
        required: '请输入银行卡号',
        digits: '银行卡号必须为数字',
        rangelength: '银行卡号长度必须在16到19之间'
      }
    };
    // 创建实例对象

    this.WxValidate = new WxValidate(rules, messages);
  },

  changeBank: function (event) {
    let index = event.detail.value;

    this.setData({
      select_bank_name: this.data.banks[index].name,
      'formData.bank_id': this.data.banks[index].id
    });
  },

  formSubmit: function (event) {
    this.setData({
      'formData.card_name': event.detail.value.card_name,
      'formData.card_number': event.detail.value.card_number
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
    wx.showLoading({
      title: '编辑中',
    });

    // 提交数据到后台
    util.post(api.BankUpdateUrl, this.data.formData).then(res => {
      wx.hideLoading();
      wx.showToast({
        title: '编辑成功',
        icon: 'success',
        duration: 2000,
        success: function () {
          setTimeout(() => {
            wx.redirectTo({
              url: '../bank/index'
            })
          }, 1500)
        }
      });
    });
  },
  getBanks: function () {
    util.get(api.BanksUrl).then(res => {
      this.setData({
        banks: res.data.data
      });
      wx.hideLoading();
    });
  },
})