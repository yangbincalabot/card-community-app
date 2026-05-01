const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";

Page({

    /**
     * 页面的初始数据
     */
    data: {
        carte_id: 0, // 名片id
        departments: [],
        select_text: '',
        select_index: 0,
        department_id: 0, // 部门id
        id: 0,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.initValidate();
        let carte_id = parseInt(options.carte_id);
        if (!carte_id) {
            wx.navigateBack()
        }
        this.setData({
            carte_id: carte_id
        });
        this.getDepartment();
        let id = options.id;
        if (id > 0) {
            this.setData({
                id: id
            });
        }
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

    getDepartment: function () {
        util.get(api.DepartmentIndexUrl).then(response => {
            let departments = response.data.data.departments;


            // 如果没有部门，先添加
            if (departments.length === 0) {
                wx.showToast({
                    title: '请先添加部门',
                    icon: 'none',
                    mask: true,
                });

                setTimeout(() => {
                    wx.navigateTo({
                        url: '../add/index',
                        success: res => {
                            res.eventChannel.on('ADD_DEPARTMENT', (department) => {
                                this.setData({
                                    select_text: department.name,
                                    department_id: department.id,
                                });
                                this.getDepartment();

                            })
                        }
                    });
                }, 1500);
                return;
            }


            this.setData({
                departments: departments,
            });
            if(this.data.id > 0){
                this.getDetail();
            }
        })
    },


    changeDepartment: function (event) {
        let index = event.detail.value;
        let currentSelect = this.data.departments[index];
        this.setData({
            select_text: currentSelect.name,
            select_index: index,
            department_id: currentSelect.id,
        })
    },

    initValidate() {
        let rules = {
            department_id: {
                min: 1
            },
        };

        let message = {
            name: {
                min: '请选择所在部门',
            },
        };
        //实例化当前的验证规则和提示消息
        this.WxValidate = new WxValidate(rules, message);
    },

    formSubmit: function () {
        let formData = {
            carte_id: this.data.carte_id,
            department_id: this.data.department_id,
            id: this.data.id
        };

        // 验证表单
        if (!this.WxValidate.checkForm(formData)) {
            let error = this.WxValidate.errorList[0];
            wx.showToast({
                title: error.msg,
                icon: 'none',
                duration: 2000,
            });
            return false
        }

        util.post(api.DepartmentBindUrl, formData).then(response => {
            wx.showToast({
                title: '保存成功'
            });
            setTimeout(() => {
                wx.navigateBack();
            }, 1500);
        })

    },
    getDetail: function () {
        util.get(api.DepartmentBindOffUrl, {id: this.data.id}, false).then(response => {
            let carteDepartment = response.data.data.carteDepartment;
            if(carteDepartment){
                let index = this.data.departments.findIndex(function(value, index){
                    return value.id === carteDepartment.department_id;
                });
                if(index > -1){
                    this.setData({
                        department_id: carteDepartment.department_id,
                        select_text: this.data.departments[index].name,
                        select_index: index,
                    })
                }
            }
        })
    }
})