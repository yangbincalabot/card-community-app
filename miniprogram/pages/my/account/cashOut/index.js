const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate";

Page({

  /**
   * 页面的初始数据
   */
  data: {
      userBalance: {
          money: '0.00'
      },
      userBanks: {},
      formData: {
          user_bank_id: '',
          money: 0,
          card_name: '',
          card_number: '',
          cash_password: '' // 支付密码
      },
      select_bank_name: '选择银行卡',

      mask: false,
      array: [],
      bott: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getUserBalance();
    this.initValidate();
      if(options.select !== undefined){
          this.setData({
              'select_bank_name': options.select,
              'formData.user_bank_id': options.user_bank_id
          });
      }

      console.log(wx.getStorageSync('IS_SELECT_BANK'));
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
    this.getUserBankInfo();
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

 

  getUserBalance: function () {
    util.get(api.UserBalanceUrl).then(res => {
      this.setData({
        userBalance: res.data.data
      })
    }).catch(error => {
      console.log(error)
    });
  },

  getUserBankInfo: function () {
    util.get(api.UserBankUrl).then((res) => {
      let response = res.data.data;
      // 如果没有银行卡，跳转到添加页面
      if(response.length === 0){
          wx.setStorageSync('FIRST_ADD_BANK', true);
          wx.showToast({
              title: '请先添加银行卡',
              icon: 'none',
              duration: 2000
          });

        setTimeout(() => {
            wx.redirectTo({
              url: '../addBank/index'
            })
        }, 2000);

        return;
      }


      let userBanks = [];
      for(let i = 0; i < response.length; i++){
          userBanks.push({
              user_bank_id: response[i].id,
              bank_name: response[i].bank.name + '-'+ response[i].card_name +'  (' + response[i].card_tail.substr(-4) + ')',
              card_name: response[i].card_name,
              card_number: response[i].card_number
          })
      }

      this.setData({
        userBanks: userBanks
      });

    });
  },

  changeBank: function (event) {
    let index = event.detail.value;

    this.setData({
      select_bank_name: this.data.userBanks[index].bank_name,
      'formData.user_bank_id': this.data.userBanks[index].user_bank_id,
      'formData.card_name': this.data.userBanks[index].card_name,
      'formData.card_number': this.data.userBanks[index].card_number
    });
  },
  formSubmit: function (event) {
    this.setData({
      'formData.money': event.detail.value.money,
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

    if(this.data.formData.money <= 0){
      wx.showToast({
        title: '提现金额不能小于0',
        icon:'none',
        duration: 2000,
      });
      return false
    }



    if(parseFloat(this.data.formData.money) > parseFloat(this.data.userBalance.balance.money)){
      wx.showToast({
        title: '提现金额不能大于可用金额',
        icon:'none',
        duration: 2000,
      });
      return false
    }

    // 显示密码框
    this.setData({mask: true});
    return false;

  },

  /**
   * 表单-验证字段
   */
  initValidate: function() {

    /**
     * 4-2(配置规则)
     */
    const rules = {

        user_bank_id: {
            required: true,
      },
      money: {
        required: true,
        money: true
      }

    };
    // 验证字段的提示信息，若不传则调用默认的信息
    const messages = {

        user_bank_id: {
            required: '请选择银行',
      },
      money: {
        required: '请输入提现金额',
        money: '请输入提现金额'
      }
    };
    // 创建实例对象

    this.WxValidate = new WxValidate(rules, messages);
  },

  onAllMoney: function () {
      if(this.data.userBalance.balance.money > 0){
          this.setData({
            'formData.money': this.data.userBalance.balance.money
          })
      }
  },

    navigatorToUrl:function (e) {
        let _url = e.currentTarget.dataset.url;
        if(_url){
            wx.navigateTo({
                url: _url
            })
        }
    },

    getPassword:function(e){
        let value = e.currentTarget.dataset.value;
        if (this.data.array.length < 6) {
            this.data.array.push(value)
        }
        if (this.data.array.length === 6) {
            // 存放密码
            this.data.formData.cash_password = this.data.array.join('');
            this.data.mask = false;
            this.data.array = [];
            this.data.bott = '';

            wx.showLoading({
                title: '提交中',
            });


            // 添加到后台
            util.post(api.UserWithdrawAddUrl, this.data.formData).then(res => {
                wx.hideLoading();
                wx.showToast({
                    title: '提交成功',
                    icon: 'success',
                    duration: 2000,
                });

                setTimeout(() => {
                    wx.redirectTo({
                        url: '../cashOutSuccess/index'
                    })
                }, 2000)
            });
        }
        this.setData({
            mask: this.data.mask,
            array: this.data.array,
            bott: this.data.bott
        });
    },
    reset:function() {
        this.data.array = []
        this.setData({
            array: this.data.array
        })
    },
    backspace:function() {
        this.data.array.pop()
        this.setData({
            array:this.data.array
        })
    },
    masks:function(){
        let that = this
        this.data.mask = true
        setTimeout(function () {
            that.data.bott = 'bot'
        }, 50)
        this.setData({
            mask: this.data.mask,
            bott: that.data.bott
        })
    },
    maskss:function() {
        this.data.mask = false
        this.data.bott = ''
        this.data.array = []
        this.setData({
            mask: this.data.mask,
            bott: this.data.bott,
            array: this.data.array
        })
    },
})