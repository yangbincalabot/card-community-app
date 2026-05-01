const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
import WxValidate from '../../../utils/validate.js'

Page({

    /**
     * 页面的初始数据
     */
    data: {
        agents: [],
        formData: {
            agent_id: 0,
            province: '',
            city: '',
            district: '',
            address: ''
        },
        address: '',
        has_apply_stay: true,
        contact_number: '',
        is_agree: true, // 是否同意协议
        can_push: true, // 是否提交
        button_text: '',
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        wx.showLoading();
        this.initValidate();
        this.getAgents();
        this.getUserApplyInfo();
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

 
    getAgents: function () {
        util.get(api.AgentIndexList).then(res => {
            console.log(res);
            let agents = res.data.data.agents;
            let contact_number = res.data.data.contact_number;
            this.setData({
                agents: agents,
                'formData.agent_id': agents[0].id, // 默认选中第一个
                contact_number: contact_number
            });
            wx.hideLoading();
        });
    },
    changeAgent: function (event) {
        let agent_id = event.currentTarget.dataset.id;
        this.setData({
            'formData.agent_id': agent_id
        })
    },
    navigatorToUrl: function (e) {
        let _url = e.currentTarget.dataset.url;
        if (_url) {
            wx.navigateTo({
                url: _url
            })
        }
    },

    bindRegionChange: function (event) {
        this.setData({
            'formData.province': event.detail.code[0],
            'formData.city': event.detail.code[1],
            'formData.district': event.detail.code[2],
            'address': `${event.detail.value[0]}-${event.detail.value[1]}-${event.detail.value[2]}`
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
            agent_id: {
                min: 1,
            },
            name: {
                required: true,
            },
            mobile: {
                required: true,
                tel: true,
            },
            id_card: {
                required: true,
                idcard: true
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
            agent_id: {
                min: '请选择代理类型'
            },
            name: {
                required: '请输入姓名',
            },
            mobile: {
                required: '请输入手机号码',
                tel: '请输入正确的手机号码',
            },
            id_card: {
                required: '请输入身份证号码',
                idcard: '身份证号码格式不正确'
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
    },

    formSubmit: function (event) {
        this.setData({
            'formData.name': event.detail.value.name,
            'formData.mobile': event.detail.value.mobile,
            'formData.id_card': event.detail.value.id_card,
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


        // 检查是否有未审核的记录，有则不让申请
        if (this.data.is_agree === false) {
            wx.showToast({
                title: '请同意服务协议',
                icon: 'none',
                duration: 2000,
            });
            return false
        }

        wx.showLoading({
            title: '提交中'
        });

        // 添加到后台
        util.post(api.ApplyAgentAddUrl, this.data.formData).then(res => {
            wx.hideLoading();
            wx.showToast({
                title: '已提交，待审核',
                icon: 'success',
                duration: 2000,
                success: function () {
                    setTimeout(() => {
                        wx.redirectTo({
                            url: '../index/index'
                        })
                    }, 1500)
                }
            });
        });
    },
    getUserApplyInfo: function () {
        util.get(api.ApplyAgentChekUrl).then(res => {
            this.setData({
                has_apply_stay: res.data.data
            });
            // 跳转结果页面
            if (this.data.has_apply_stay && parseInt(this.data.has_apply_stay.status) === 1) {
                this.setData({
                    can_push: false,
                    button_text: '请勿重复申请'
                });
            }else if(this.data.has_apply_stay && parseInt(this.data.has_apply_stay.status) === 2){
                this.setData({
                    can_push: false,
                    button_text: '已提交申请，待审核'
                });
            }
        }).catch(error => {
            console.log(error)
        })
    },
    changAgree: function (event) {
        let is_agree = false
        if(event.detail.value.length > 0 && event.detail.value[0] === '1'){
            is_agree = true
        }
        this.setData({
            is_agree: is_agree
        })
    }
})