const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";
Page({

    /**
     * 页面的初始数据
     */
    data: {
        formData: {
            is_show: true,
            images: [],
            image: '',
        },
        image_url: '',
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.initValidate()
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

    changeShow: function(){
        this.setData({
            'formData.is_show': !this.data.formData.is_show
        })
    },

    UploadImage: function (event) {
        let type = '';
        if (event.currentTarget.dataset.type) {
            type = event.currentTarget.dataset.type;
        }

        switch (type) {
            case 'avatar':
                // 头像上传
                util.fliesUpload().then((respond) => {
                    let uploadResponse = JSON.parse(respond.data);
                    this.setData({
                        'formData.image': uploadResponse.storage_path,
                        'image_url': api.ResourceRootUrl + uploadResponse.relative_url
                    });
                    console.log(this.data.formData);

                }).catch((err) => {
                    console.log(err)
                });
                break;
            case "images":
                // 相册上传
                let currentImages = this.data.formData.images;
                let currentNum = currentImages.length;
                let totalNum = 9;
                if (currentNum === totalNum) {
                    wx.showToast({
                        title: '最多只能上传' + totalNum + '张图片！',
                        duration: 2000
                    });
                    return;
                }
                util.multipartFliesUpload().then((respond) => {
                    let uploadUrlData = respond;
                    let uploadNum = uploadUrlData.length;
                    let _imagesData = currentImages.concat(uploadUrlData);
                    // 判断上传的数量是否超过总数
                    if ((currentNum + uploadNum) > totalNum) {
                        _imagesData = _imagesData.slice(0, totalNum);
                    }

                    this.setData({
                        'formData.images': _imagesData
                    });

                }).catch((err) => {
                    console.log(err)
                });
                break;
        }
    },

    initValidate() {
        let rules = {
            image: {
                required: true,
            },
            title: {
                required: true,
                maxlength: 30
            },
            price: {
                required: true,
                money: true,
            },
            content: {
                required: true,
            },
            

        };

        let message = {
            image: {
                required: "请输入上传封面",
            },
            title: {
                required: '请输入标题',
                maxlength: '标题长度不能超过30个字符'
            },
            price: {
                required: "请输入商品价格",
                money: '商品价格不合法'
            },
            content: {
                required: "请输入商品详情",
            },
        };
        //实例化当前的验证规则和提示消息
        this.WxValidate = new WxValidate(rules, message);
    },

    formSubmit: function(event){
        let postData = Object.assign(event.detail.value, this.data.formData);
        // 验证表单
        if (!this.WxValidate.checkForm(postData)) {
            let error = this.WxValidate.errorList[0];
            wx.showToast({
                title: error.msg,
                icon: 'none',
                duration: 2000,
            });
            return false
        }

        wx.showLoading({
            title: '添加中',
        });
        util.post(api.BusinessGoodsAddUrl, postData).then((res) => {
            wx.hideLoading();
            wx.showToast({
                title: '添加成功',
                icon: 'success',
                duration: 2000,
                success: function () {
                    setTimeout(() => {
                        wx.redirectTo({
                            url: '../index/index'
                        })
                    }, 2000)
                }
            });
        })

    },

    deleteImage: function (event) {
        let index = event.currentTarget.dataset.index;
        if (index >= 0) {
            this.data.formData.images.splice(index, 1);
            this.setData({
                'formData.images': this.data.formData.images
            })
        }
    },
})