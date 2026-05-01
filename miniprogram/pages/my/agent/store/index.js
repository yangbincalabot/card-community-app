const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from '../../../../utils/validate.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
      store: {},
      area: '', // 所在地区
      address: '', // 详细地址,
      formData: {
        contact_name: '',
        contact_mobile: '',
        image: '', // 门店图片
      },
      image: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      this.getStoreInfo();
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


  getStoreInfo: function () {
    util.get(api.UserStoreDetailUrl).then(res => {
        let full_address = res.data.data.full_address.split('-');
        let address = full_address.pop();
        let area = full_address.join('-');

        this.setData({
          store: res.data.data,
          area: area,
          address: address,
          'formData.contact_name': res.data.data.contact_name,
          'formData.contact_mobile': res.data.data.contact_mobile,
          'formData.image': res.data.data.image,
          image: res.data.data.image
        })
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

      contact_name: {
        required: true,
      },
      contact_mobile: {
        required: true,
        tel: true
      },

    };
    // 验证字段的提示信息，若不传则调用默认的信息
    const messages = {

      contact_name: {
        required: '请输入联系人',
      },
      contact_mobile: {
        required: '请选择银行',
        tel: '号码格式错误'
      },
    };
    // 创建实例对象

    this.WxValidate = new WxValidate(rules, messages);
  },


  formSubmit: function (event) {
    this.setData({
      'formData.contact_name': event.detail.value.contact_name,
      'formData.contact_mobile': event.detail.value.contact_mobile,
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
      title: '修改中',
    });


    util.post(api.UserStoreUpdateUrl, this.data.formData).then(res => {
      wx.hideLoading();
      wx.showToast({
        title: '修改成功',
        icon: 'success',
        duration: 2000
      });
    });
  },

  UploadImage: function (event) {
    util.fliesUpload().then((respond) => {
      let uploadResponse = JSON.parse(respond.data);
      console.log(uploadResponse);
      this.setData({
        image: api.ResourceRootUrl + uploadResponse.relative_url,
        'formData.image': uploadResponse.storage_path
      });
    }).catch((err) => {
      console.log(err)
    })
  },
})