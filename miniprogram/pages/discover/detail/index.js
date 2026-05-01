// pages/discover/detail/index.js
import parser from "../../../utils/htmlParse/parser";

const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const html = require('../../../utils/htmlParse/parser.js');
const app = getApp();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        ResourceRootUrl: api.ResourceRootUrl,
        activityDetail: {},
        collectionStatus: false,
        collectionType: 2
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        let _id = options.id;
        that.getActivityDetail(_id);
        that.getCollectionStatus(_id);
        if(options){
            if(options.is_share && options.is_share==1){
                console.log('onShow.options.is_share==1',options);
                wx.showModal({
                    title: '提示',
                    content: '请通过右上角菜单中的返回首页按钮访问其他页面',
                    success (res) {
                        if (res.confirm) {

                        } else if (res.cancel) {

                        }
                    }
                })
            }
        }
    },

    getActivityDetail(id) {
        let that = this;
        let _id = id;
        util.post(api.ActivityBigDetail, {id, _id})
            .then(response => {
                let _data = response.data.data;
                console.log(_data)
                if (!_data || _data.length == 0) {
                    wx.showToast({title: '该页面已过期', icon: 'none', duration: 800});
                    setTimeout(function () {
                        wx.navigateBack({
                            delta: 1
                        })
                    }, 700);
                    return false;
                };
                let _content = that.getContent(_data.content);
                html.default.definedCustomTag({figure: 'div', figcaption: ''});
                let _nodes = html.default.getRichTextJson(_content);
                let _newContent = _nodes.children;
                that.setData({
                    contentNode: _newContent,
                    activityDetail: _data
                })
            });
    },

    getContent: function (content) {
        let that = this;
        let html = content
            .replace(/\/>/g, '>')
            .replace(/<img([\s\w"-=\/\.:;]+)((?:(height="[^"]+")))/ig, '<img$1')
            .replace(/<img([\s\w"-=\/\.:;]+)((?:(width="[^"]+")))/ig, '<img$1')
            .replace(/<img([\s\w"-=\/\.:;]+)((?:(style="[^"]+")))/ig, '<img$1')
            .replace(/<img([\s\w"-=\/\.:;]+)((?:(alt="[^"]+")))/ig, '<img$1')
            .replace(/<img([\s\w"-=\/\.:;]+)/ig, '<img$1 style="max-width: 100%;margin:0 auto; height:auto; border-radius: 8Px;"');
        return html;
    },

    playPhone: function (e) {
        let that = this;
        let _phone = e.currentTarget.dataset.phone;
        if (!_phone) {
            wx.showToast({title: '主办方暂未设置电话', icon: 'none', duration: 800});
            return false;
        }
        wx.makePhoneCall({
            phoneNumber: _phone
        })
    },

    /**
     * 查看登录用户是否已收藏
     */

    getCollectionStatus: function (_id) {
        let that = this;
        let postParams = {};
        postParams.type = that.data.collectionType;
        postParams.info_id = _id;
        util.post(api.CollectionGetStatusUrl, postParams)
            .then(response => {
                let _data = response.data.data;
                console.log(_data)
                if (_data && _data.status == 1) {
                    that.setData({
                        collectionStatus: true
                    })
                }
            });
    },

    /**
     * 用户收藏
     */
    clickCollection: function () {
        let that = this
        let postParams = {};
        postParams.info_id = that.data.activityDetail.id;
        postParams.type = that.data.collectionType;
        util.post(api.CollectionUrl, postParams)
            .then(response => {
                let status = that.data.collectionStatus;
                let _collectionStatus = true
                if (status) {
                    _collectionStatus = false;
                }
                that.setData({
                    collectionStatus: _collectionStatus
                })
                wx.showToast({
                    title: '操作成功',
                    icon: 'success',
                    duration: 1000
                })
            });
    },

    navigateToUrl: function (e) {
        let that = this;
        let _url = e.currentTarget.dataset.url;
        wx.navigateTo({
            url: _url
        })
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

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {
        let that = this;
        let userInfo = that.data.activityDetail.requset_user;
        let _title = that.data.activityDetail.title;
        let _id = that.data.activityDetail.id;
        let _cover_image = that.data.activityDetail.cover_image;
        let _shareUrl = '/pages/spread/index/index?scene=type@1|id@'+_id;

        if (userInfo && userInfo != null) {
            let user_id = userInfo.id;
            _shareUrl += '|user_id@'+user_id;
        }
        console.log('_shareUrl',_shareUrl);
        return {
            title: _title,
            path: _shareUrl,
            imageUrl: _cover_image
        }
    }
})