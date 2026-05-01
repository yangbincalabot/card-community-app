// pages/my/supply/create/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({
    /**
     * 页面的初始数据
     */
    data: {
        ResourceRootUrl: api.ResourceRootUrl,
        detail:{},
        images: [],
        totalNum: 9,
        typeArr: [],
        typeIndex: 0,
        type_title:'请选择'
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        let _id = options.id;
        if (_id) {
            that.getDetail(_id);
        }
        setTimeout(() => {
          that.getTypeArr();
        },800);
        
    },

    getDetail: function (id) {
        let that = this;
        util.post(api.SupplyDetail, {id: id})
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                if (!_data || _data === 'undefind') {
                    return false;
                }
                that.setData({
                    id: id,
                    detail: _data,
                    images: _data.images,
                    type: _data.type
                });
            });
    },

    getTypeArr: function () {
        let that = this;
        util.post(api.SupplyType, {})
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                if (_data && _data.length > 0) {
                    let _type = that.data.type;
                    let _index = that.data.typeIndex;
                    let _type_title = that.data.type_title;
                    if (_type && _type > 0) {
                      _index = util.getObjKeyById(_data,_type);
                        _type_title = _data[_index].title;
                    }
                    that.setData({
                        typeArr: _data,
                        typeIndex: _index,
                        type_title: _type_title
                    });
                }
            });
    },

    formSubmit: function (e) {
        let that = this;
        let postData = e.detail.value;
        let _id = that.data.id;
        let url = api.SupplyAdd;
        let success_title = '创建成功';
        if (_id) {
            url = api.SupplyEdit;
            success_title = '更新成功';
            postData.id = _id;
        }
        postData.type = that.data.type;
        postData.images = that.data.images;
        util.post(url, postData)
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                wx.showToast({
                    title: success_title,
                    icon: 'none',
                    duration: 1000
                });
                setTimeout(function () {
                    wx.navigateBack({
                      url: '../index/index'
                    })
                }, 500)
            });
    },

    bindPickerChange: function (e) {
        let that = this;
        let _key = e.detail.value;
        let _typeArr = that.data.typeArr;
        that.setData({
            type: _typeArr[_key].id,
            type_title: _typeArr[_key].title,
        });
    },


    UploadImage: function (event) {
        let that = this;
        let currentImages = that.data.images;
        let currentNum = currentImages.length;
        let totalNum = that.data.totalNum;
        if (currentNum == totalNum) {
            wx.showToast({
                title: '最多只能上传' + totalNum + '张图片！',
                duration: 1000
            });
            return false;
        }
        let lastNum = totalNum - currentNum;
        util.multipartFliesUpload(lastNum).then((respond) => {
            let uploadUrlData = respond;
            // let _imagesData = uploadUrlData[0];
            let _imagesData = currentImages.concat(uploadUrlData);
            console.log(uploadUrlData);
            that.setData({
                'images': _imagesData,
            });
            wx.showToast({
                title: '上传成功',
                duration: 1000
            });
        }).catch((err) => {
            console.log(err)
            wx.showToast({
                title: '上传失败！',
                duration: 1000
            });
        })
    },

    DeleteImage: function (event) {
        console.log('444');
        let that = this;
        let _key = event.currentTarget.dataset.id;
        let _imagesData = that.data.images;
        _imagesData.splice(_key, 1);
        that.setData({
            'images': _imagesData,
        });
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
})