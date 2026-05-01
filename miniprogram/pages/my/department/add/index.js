const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";

Page({

    /**
     * 页面的初始数据
     */
    data: {
        formData: {
            name: '',
        }
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
    formSubmit: function (event) {
        // 验证表单
        let formData = event.detail.value;
        if (!this.WxValidate.checkForm(formData)) {
            let error = this.WxValidate.errorList[0];
            wx.showToast({
                title: error.msg,
                icon:'none',
                duration: 2000,
            });
            return false
        }

        util.post(api.DepartmentStoreUrl, formData).then(response => {
            const eventChannel = this.getOpenerEventChannel();
            wx.showToast({
                title: '添加成功'
            });
            setTimeout(() => {
                eventChannel.emit('ADD_DEPARTMENT', response.data.data.department);
                wx.navigateBack();
            }, 1500)
        })
    },
    initValidate() {
        let rules = {
            name: {
                required: true,
                maxlength: 30
            },

        };

        let message = {
            name: {
                required: '请输入部门名称',
                maxlength: '部门名称长度不能超过30个字符'
            },
        };
        //实例化当前的验证规则和提示消息
        this.WxValidate = new WxValidate(rules, message);
    },
})