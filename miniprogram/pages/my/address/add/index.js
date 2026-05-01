const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from '../../../../utils/validate.js'

Page({

    /**
     * 页面的初始数据
     */
    data: {
        formData: {
            contact_name: '',
            contact_phone: '',
            province: '',
            city: '',
            district: '',
            address: '',
            is_default: false,
        },
        address: ''
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

 
    bindRegionChange: function (event) {
        this.setData({
            'formData.province': event.detail.code[0],
            'formData.city': event.detail.code[1],
            'formData.district': event.detail.code[2],
            'address': `${event.detail.value[0]}-${event.detail.value[1]}-${event.detail.value[2]}`
        });
        console.log(this.data.formData.province);
        console.log(this.data.formData.city);
        console.log(this.data.formData.district);
    },
    switchIsDefault: function (event) {
        this.setData({
            'formData.is_default': event.detail.value
        })
        console.log(this.data.formData)
    },
    formSubmit: function (event) {
        this.setData({
            'formData.contact_name': event.detail.value.contact_name,
            'formData.contact_phone': event.detail.value.contact_phone,
            'formData.address': event.detail.value.address,
        });

        // 验证表单
        if (!this.WxValidate.checkForm(this.data.formData)) {
            let error = this.WxValidate.errorList[0];
            wx.showToast({
                title: error.msg,
                icon: 'none',
                duration: 2000,
            });
            return false
        }
        console.log(this.data.formData);

        wx.showLoading({
            title: '添加中',
        });

        // 添加到后台
        util.post(api.UserAddressAddUrl, this.data.formData).then(res => {
            wx.hideLoading();
            wx.showToast({
                title: '添加成功',
                icon: 'success',
                duration: 2000,
                success: () => {
                    setTimeout(() => {
                        /*wx.redirectTo({
                            url: '../index'
                        })*/
                        wx.navigateBack({
                            delta: 1
                        })

                    }, 2000)
                }

            });
        });
    },

    /**
     * 表单-验证字段
     */
    initValidate: function () {

        /**
         * 4-2(配置规则)
         */
        const rules = {

            contact_name: {
                required: true,
            },
            contact_phone: {
                required: true,
                tel: true,
            },
            province: {
                required: true
            },
            city: {
                required: true
            },
            district: {
                required: true
            },
            address: {
                required: true,
            },


        };
        // 验证字段的提示信息，若不传则调用默认的信息
        const messages = {

            contact_name: {
                required: '请输入收货人姓名',
            },
            contact_phone: {
                required: '请输入收货人联系号码',
                tel: '请输入正确的手机号码',
            },
            province: {
                required: '请选择所在地区'
            },
            city: {
                required: '请选择所在地区'
            },
            district: {
                required: '请选择所在地区'
            },
            address: {
                required: '请输入详细地址'
            },

        };
        // 创建实例对象

        this.WxValidate = new WxValidate(rules, messages);
    }

})