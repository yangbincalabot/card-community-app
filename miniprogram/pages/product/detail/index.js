// pages/product/detail/index.js
import parser from "../../../utils/htmlParse/parser";

const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const html = require('../../../utils/htmlParse/parser.js');

Page({

    /**
     * 页面的初始数据
     */
    data: {
        focus: false,
        inputValue: '',
        imgUrls: [
            '/images/prodetail_banner.png',
            '/images/prodetail_banner.png',
            '/images/prodetail_banner.png'
        ],
        collectionType: 1,
        indicatorDots: false,
        autoplay: false,
        interval: 5000,
        duration: 1000,

        currentId: '',
        responseData: '',
        hasCheckedSpecs: false,
        formParams: {amount: 1},
        skuData: {},
        currentCheckedSku: {},
        currentSkuData: {},
        openType: '',


        mask: false,
        status: false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        let _id = options.id;
        that.setData({
            currentId: _id
        });
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

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function (options) {
        let that = this;
        let _currentId = that.data.currentId;
        if (_currentId != '') {
            that.getProductDetail({id: _currentId});
        }
    },

    getProductDetail: function (options) {
        let that = this;
        let postParams = {};
        if (options != undefined) {
            postParams = options;
        }

        util.post(api.ProductDetailUrl, postParams)
            .then(response => {
                let _responseData = response.data.data;
                let _skuData = _responseData.skus_data;
                let _content = _responseData.description;
                html.default.definedCustomTag({figure: 'div', figcaption: ''});
                let _nodes = html.default.getRichTextJson(_content);
                let _newContent = _nodes.children;
                that.setData({
                    responseData: _responseData,
                    contentNode: _newContent,
                    skuData: _skuData,
                });

                let _currentSkuData = _responseData.default_checked_sku;
                let _skuTypeId = _currentSkuData.sku_category_id;
                let _skuId = _currentSkuData.id;
                that.setCurrentCheckedSkuParams('skuData.' + _skuTypeId, _skuId);
                that.setCurrentCheckedSkuParams('currentSkuData', _currentSkuData);
                that.setFormParams('sku_id', _skuId);
                util.setCurrentNavigationBarTitle(_responseData.title)
            });
    },

    addToCart: function () {
        let that = this;
        // 检查是否选择了规格
        let _currentCheckedSku = that.data.currentCheckedSku;
        console.log('_currentCheckedSku', _currentCheckedSku);
        let _dataInfo = that.data;
        console.log('thatData', _dataInfo);
        let postParams = that.data.formParams;
        util.post(api.CartAddUrl, postParams)
            .then(response => {
                wx.showToast({
                    title: '加入成功',
                    icon: 'success',
                    duration: 2000
                })
            });
    },

    openDrawer: function (e) {
        let that = this;
        let _openType = e.currentTarget.dataset.type;
        console.log('_openType', _openType);
        that.openDrawerBox(_openType);
    },


    openDrawerBox: function (_openType) {
        let that = this;
        that.setData({
            openType: _openType,
            mask: true,
            status: true
        });
    },

    closeDrawerBox: function () {
        this.setData({
            openType: '',
            mask: false,
            status: false
        })
    },


    skuChecked: function (e) {
        let that = this;
        console.log(e);
        let _skuTypeId = e.currentTarget.dataset.skutype;
        let _skuId = e.currentTarget.dataset.skuid;
        let _skuData = that.data.skuData;
        console.log(_skuId);
        if (_skuId) {
            let _currentSkuTypeData = util.getObjInfoDataByname(_skuData, 'id', _skuTypeId);
            let _currentSkuData = util.getObjInfoDataByname(_currentSkuTypeData.sku_arr, 'id', _skuId);
            console.log('_currentSkuTypeData', _currentSkuTypeData);
            console.log('_currentSkuData', _currentSkuData);
            that.setCurrentCheckedSkuParams('skuData.' + _skuTypeId, _skuId);
            that.setCurrentCheckedSkuParams('currentSkuData', _currentSkuData);
            that.setFormParams('sku_id', _skuId);
        }
        return;
    },

    confirmSkuChecked: function () {
        let that = this;

        that.closeDrawerBox();
    },

    setCurrentCheckedSkuParams: function (_str, _value) {
        let that = this;
        let _firstStr = 'currentCheckedSku';
        that.setDataParams(_firstStr, _str, _value)
    },

    setFormParams: function (_str, _value) {
        let that = this;
        let _firstStr = 'formParams';
        that.setDataParams(_firstStr, _str, _value)
    },

    setDataParams: function (_firstStr, _secondStr, _value) {
        let that = this;
        let _current_str = _firstStr + '.' + _secondStr;
        that.setData({
            [_current_str]: _value,
        });
    },

    // 立即领取优惠券
    immediatelyGet: function (e) {
        let that = this;
        let _id = e.currentTarget.dataset.id;
        if (!_id) {
            return;
        }
        let _couponData = that.data.responseData.coupons;
        let postParams = {id: _id};
        util.post(api.CouponsGetUrl, postParams)
            .then(response => {
                wx.showToast({
                    title: '领取成功',
                    icon: 'success',
                    duration: 2000
                });
                let _couponKey = util.getObjKeyById(_couponData, _id);
                _couponData[_couponKey].has_get = 1;
                let _couponStr = 'responseData.coupons';
                that.setData({
                    [_couponStr]: _couponData
                })
            });
    },

    /**
     * 查看登录用户是否已收藏
     */

    getCollectionStatus: function (_id) {
        let that = this;
        let postParams = {};
        postParams.type = that.data.collectionType;
        postParams.operation_id = _id;
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
        postParams.operation_id = that.data.currentId;
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

    buyNow: function () {
        let that = this;
        let _currentCheckedSku = that.data.currentCheckedSku;
        let _skuId = _currentCheckedSku.currentSkuData.id;
        if(_skuId){
            let _currentCheckedItems = {sku_id:_skuId,amount:1};
            wx.setStorage({
                key: 'buy_now_sku_id',
                data: _currentCheckedItems
            });
            wx.navigateTo({
                url: '/pages/cart/buy_now_settlement/index'
            });
        }
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
        let _responseData = this.data.responseData;
        let _shareUrl = '/pages/spread/index/index?scene=type@2|id@'+_responseData.id;
        if(_responseData.requset_user != ''){
            _shareUrl += '|user_id@'+_responseData.requset_user.id;
        }
        let _cover_image = _responseData.image;
        return {
            title: _responseData.title,
            imageUrl: _cover_image,
            path: _shareUrl
        }
    }
})