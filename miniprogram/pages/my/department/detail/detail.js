const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";

Page({

    /**
     * 页面的初始数据
     */
    data: {
        id: 0,
        detail: {},
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.initValidate();
        let id = parseInt(options.id);
        if(id <= 0){
            wx.navigateBack();
        }
        this.setData({
            id
        });
        this.getDetail();
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

    getDetail: function () {
        util.get(api.DepartmentDetailUrl, {id: this.data.id}).then(response => {
            this.setData({
                detail : response.data.data.department
            })
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
    formSubmit: function (event) {
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
        formData.id = this.data.detail.id;
        formData._method = 'PATCH';
        util.post(api.DepartmentUpdateUrl, formData).then(response => {
            wx.showToast({
                title: '编辑成功',
            })
        })
    },
    onDelete : function() {
        wx.showModal({
            title: '提示',
            content: '确定删除吗？',
            success: res => {
                if (res.confirm) {
                    util.post(api.DepartmentDeleteUrl, {id : this.data.id, _method: 'DELETE'}).then(response => {
                        wx.showToast({
                            title: '删除成功'
                        })
                    })
                    setTimeout(() => {
                        wx.navigateBack()
                    }, 1500);
                }
            }
        })
    }
});