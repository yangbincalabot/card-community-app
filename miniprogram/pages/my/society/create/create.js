const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";
Page({

    /**
     * 页面的初始数据
     */
    data: {
        formData: {
            images: [],
            image: '',
            service_images:[],
            member_wall: [],
        },
        image_url: '',
        associations: [],
        currentIndex: 0,
        currentTitle: '无',
        pid: '',
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.getAssociationList();
        this.initValidate();
    },

    getAssociationList: function () {
        util.get(api.SelectAssociationUrl).then(res => {
            let _data = res.data.data;
            _data.list.unshift({
                id: 0,
                name: '无'
            });
            console.log(_data)
            this.setData({
                associations: _data.list,
                currentIndex: 0
            })
        })
    },

    bindPickerChange: function (e) {
        let _index = e.detail.value;
        let _associations = this.data.associations;
        this.setData({
            currentIndex: _index,
            currentTitle: _associations[_index].name,
            pid: _associations[_index].id
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


    UploadImage: function (event) {
        let type = '';
        if (event.currentTarget.dataset.type) {
            type = event.currentTarget.dataset.type;
        }

        const totalNum =  9;

        let currentNum =    0;

        switch (type) {
            case 'image':
                // 封面上传
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
                currentNum = currentImages.length;
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
            // 服务相册 
            case 'service_images':
                let currentServiceImages = this.data.formData.service_images;
                currentNum = currentServiceImages.length;
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
                    let _imagesData = currentServiceImages.concat(uploadUrlData);
                    // 判断上传的数量是否超过总数
                    if ((currentNum + uploadNum) > totalNum) {
                        _imagesData = _imagesData.slice(0, totalNum);
                    }

                    this.setData({
                        'formData.service_images': _imagesData
                    });

                }).catch((err) => {
                    console.log(err)
                });
                break;
                
            // 会员墙
            case  'member_wall':
                let currentMemberWallImages = this.data.formData.member_wall;
                util.multipartFliesUpload().then((respond) => {
                    let uploadUrlData = respond;
                    let uploadNum = uploadUrlData.length;
                    let _imagesData = currentMemberWallImages.concat(uploadUrlData);
                    // 判断上传的数量是否超过总数
                    if ((currentNum + uploadNum) > totalNum) {
                        _imagesData = _imagesData.slice(0, totalNum);
                    }

                    this.setData({
                        'formData.member_wall': _imagesData
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
            name: {
                required: true,
                maxlength: 30
            },
            desc: {
                required: true,
            },
            service_desc: {
                required: true,
            },
            instructions: {
                required: true,
            }


        };

        let message = {
            image: {
                required: "请输入上传封面",
            },
            title: {
                required: '请输入协会名称',
                maxlength: '协会名称长度不能超过30个字符'
            },
            desc: {
                required: "请输入协会详情",
            },
            service_desc:{
                required:  "请输入服务简介"
            },

            instructions: {
                required:  "请输入入会须知"
            }
        };
        //实例化当前的验证规则和提示消息
        this.WxValidate = new WxValidate(rules, message);
    },

    formSubmit: function (event) {
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


        // if(isNaN(postData.fee) || Number(postData.fee) < 0){
        //     wx.showToast({
        //         title: '加入协会费用不合法',
        //         icon: 'none',
        //         duration: 2000,
        //     });
        //     return false
        // }

        wx.showLoading({
            title: '添加中',
        });
        postData.pid = this.data.pid;
        util.post(api.BusinessAssociationCreateUrl, postData).then((res) => {
            wx.hideLoading();
            wx.showToast({
                title: '等待审核',
                icon: 'success',
                duration: 2000,
                success: function () {
                    setTimeout(() => {
                        wx.redirectTo({
                            url: '../list/list'
                        })
                    }, 2000)
                }
            });
        })

    },

    deleteImage: function (event) {
        let index = event.currentTarget.dataset.index;
        let type = event.currentTarget.dataset.type || 'images';

        switch(type) {
            case 'images':
                if (index >= 0) {
                    this.data.formData.images.splice(index, 1);
                    this.setData({
                        'formData.images': this.data.formData.images
                    })
                }
                break;
            case 'service_images':
                if (index >= 0) {
                    this.data.formData.service_images.splice(index, 1);
                    this.setData({
                        'formData.service_images': this.data.formData.service_images
                    })
                }
                break;
            
            case 'member_wall':
                if (index >= 0) {
                    this.data.formData.member_wall.splice(index, 1);
                    this.setData({
                        'formData.member_wall': this.data.formData.member_wall
                    })
                }
                break;
        }

        
    },
})