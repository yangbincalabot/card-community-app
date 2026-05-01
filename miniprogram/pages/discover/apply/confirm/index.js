// pages/discover/apply/confirm/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        apply_group: [],
        new_group: [],
        activityDetail: {},
        total_people: 0,
        total_price: 0,
        is_hidden: true,
        postData: {
            'urgent_contact': '',
            'phone': ''
        },
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        let _id = options.activity_id;
        that.setData({
            id: _id
        })
        that.getActivityDetail(_id);
        that.getPostData();
    },

    getActivityDetail(id) {
        let that = this;
        let _id = id;
        util.post(api.ActivityBigDetail, {id, _id})
            .then(response => {
                let _data = response.data.data;
                console.log(_data)
                that.setData({
                    activityDetail: _data
                })
                that.getApplyGroup(_id);
            });
    },

    getApplyGroup: function (id) {
        let that = this;
        let _apply_group = wx.getStorageSync('activity_apply_group');
        if (_apply_group == 0) {
            wx.navigateTo({
                url: '../group/index?id=' + id
            })
            return false;
        }
        let group = that.data.activityDetail.group;
        let _new_group = [];
        new Promise(function (resolve) {
            resolve();
        }).then(() => {
            for (let index in group) {
                let item = group[index];
                if (_apply_group.indexOf(item.id) != -1) {
                    _new_group.push(item);
                }
            }
        }).then(() => {
            that.setData({
                new_group: _new_group,
                apply_group: _apply_group
            })
            if (_new_group && _new_group.length > 0) {
                that.getChooseApplicant(_new_group);
            }
        });
    },


    getChooseApplicant: function (_new_group) {
        let that = this;
        let _choose_applicant = wx.getStorageSync('choose_applicant');
        let param = {
            'choose_applicant': _choose_applicant,
            'new_group': _new_group,
            'is_confim': 1
        }
        util.post(api.ApplyProcess, param)
            .then(response => {
                let _data = response.data.data;
                let _total_people = response.data.total_people;
                let _total_man_num = response.data.total_man_num;
                let _total_woman_num = response.data.total_woman_num;
                let _fee = that.data.activityDetail.fee;
                console.log(response.data)
                let _total_price = 0;
                if (_fee > 0) {
                    _total_price = _total_people * _fee;
                }
                if (_data && _data.length > 0) {
                    that.setData({
                        choose_applicant: _choose_applicant,
                        new_group: _data,
                        total_people: _total_people,
                        total_price: _total_price,
                        total_man_num: _total_man_num,
                        total_woman_num: _total_woman_num,
                        is_hidden: false
                    })
                }
            });
    },

    getPostData: function () {
        let that = this;
        let _postData = wx.getStorageSync('apply_information');
        let _urgent_contact = '';
        let _phone = '';
        if (!_postData) {
            return false;
        }
        if (_postData.urgent_contact) {
            _urgent_contact = _postData.urgent_contact;
        }
        if (_postData.phone) {
            _phone = _postData.phone;
        }
        that.setData({
            'postData.urgent_contact': _urgent_contact,
            'postData.phone': _phone
        })
    },

    determine: function () {
        let that = this;
        let _postData = that.data.postData;
        let _activity_id = that.data.id;
        let _total_people = that.data.total_people;
        let _total_price = that.data.total_price;
        let _choose_applicant = wx.getStorageSync('choose_applicant');
        _postData['total_people'] = _total_people;
        _postData['total_price'] = _total_price;
        _postData['choose_applicant'] = _choose_applicant;
        _postData['activity_id'] = _activity_id;
        console.log(_postData);
        console.log(_choose_applicant);
        util.post(api.ApplyCreate, _postData)
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                if (_data.id) {
                    let _url = '/pages/success/index?id=' + _data.id;
                    if (_data.status == 2) {
                        // 待支付
                        wx.removeStorageSync('activity_apply_group');
                        wx.removeStorageSync('choose_applicant');
                        wx.removeStorageSync('apply_information');
                        _url = '/pages/pay/discover_apply_pay/index?id=' + _data.id;
                        wx.redirectTo({
                            url: _url
                        });
                        return;
                    }
                    wx.showToast({title: '报名成功', icon: 'none', duration: 800});
                    new Promise(function (resolve) {
                        resolve();
                    }).then(() => {
                        wx.removeStorageSync('activity_apply_group');
                        wx.removeStorageSync('choose_applicant');
                        wx.removeStorageSync('apply_information');
                    }).then(() => {
                        wx.redirectTo({
                            url: _url
                        });

                    })
                }
            });
    },

    payment: function () {
        let that = this;
        let _total_price = that.data.total_price;
        if (_total_price > 0) {
            // ...微信支付
            // 目前先不进行支付
            that.determine();
        } else {
            that.determine();
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
        let that = this;
        let _id = that.data.id;
        that.getApplyGroup(_id);
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