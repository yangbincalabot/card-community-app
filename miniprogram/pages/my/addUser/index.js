const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
import WxValidate from "../../../utils/validate.js";
var QQMapWX = require('../../../qqmap/qqmap-wx-jssdk.js');
var qqmapsdk;
const App = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: {},
    formData: {
      cid: 0,
      name: '',
      company_name: '',
      avatar: '',
      phone: '',
      wechat: '',
      email: '',
      introduction: '',
      industry_id: 0,
      position: '',
      open: 1, // 默认公开
      images: [],
      longitude: '',
      latitude: '',
      address_title: '',
      address_name: '',
      province: '',
      city: '',
      tags: [''],
      _method: 'PUT',
      card_color: 1,
    },
    bindInfo: {
      id: 0,
      carte_id: 0,
      company_id: 0,
    },
    industries: [[], []], // 行业数据，picker组件显示
    industry_text: '', // 行业名称
    input_address_title: '',
    avatar_url: '',
    is_open: false,
    bind_company_name: '',
    industryArray: [],// 所有行业数据
    industry_index: [0, 0], // 默认行业选择索引
    code: '',
    is_bind_phone: false, // 是否绑定手机号
    hidden: true,
    windowWidth: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      windowWidth: App.globalData.windowWidth
    })
    this.initValidate();
    wx.showLoading({
      title: '加载中',
      mask: true
    });
    this.getIndustries();
  },

  changeSellse(e) {
    let _id = e.currentTarget.dataset.id;
    this.setData({
      'formData.card_color': _id
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
    let company_name = wx.getStorageSync('BIND_COMPANY_NAME');
    let cid = wx.getStorageSync('BIND_COMPANY_ID');
    let prefect_company_name = wx.getStorageSync('PERFECT_BUSINESS_CARD');
    if (company_name && cid) {
      wx.removeStorageSync('BIND_COMPANY_NAME');
      wx.removeStorageSync('BIND_COMPANY_ID');
      this.setData({
        'formData.cid': cid,
        bind_company_name: company_name
      })
    }

    if (prefect_company_name) {
      wx.removeStorageSync('PERFECT_BUSINESS_CARD');
      this.setData({
        bind_company_name: prefect_company_name
      })
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
  initValidate() {
    let rules = {
      name: {
        required: true,
        maxlength: 20
      },
      company_name: {
        required: true,
        maxlength: 255,
      },
      // industry_id: {
      //     min: 1,
      // },
      // position: {
      //     required : true,
      //     maxLength : 255,
      // },
      phone: {
        required: true,
        tel: true
      },
      // longitude: {
      //     required: true,
      // },
      // latitude: {
      //     required: true,
      // }

    };

    let message = {
      name: {
        required: '请输入姓名',
        maxlength: '姓名长度不能超过20个字符'
      },
      company_name: {
        required: "请输入公司名称",
        maxlength: '公司名称过长'
      },
      industry_id: {
        min: "请选择行业",
      },
      position: {
        required: "请输入职务",
        maxLength: '职务过长'
      },
      phone: {
        required: "请输入手机号码",
        tel: '手机号码格式不正确',
      },
      longitude: {
        required: '请设置经纬度',
      },
      latitude: {
        required: '请设置经纬度',
      }
    };
    //实例化当前的验证规则和提示消息
    this.WxValidate = new WxValidate(rules, message);
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
            'formData.avatar': uploadResponse.storage_path,
            'avatar_url': api.ResourceRootUrl + uploadResponse.relative_url
          });

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

  getIndustries: function () {
    util.get(api.GetIndustriesUrl).then(res => {
      let response = res.data.data;
      let first_column = []; // 第一列数据
      let second_column = []; // 第二列数据
      let industries = this.data.industries;
      // 设置默认显示的数据
      if (response.length > 0) {
        first_column = response;
        if (response[0].children.length > 0) {
          second_column = response[0].children;
        }
        industries[0] = first_column;
        industries[1] = second_column;
      }
      this.setData({
        industryArray: res.data.data,
        industries: industries,
        hidden: false
      });
    })
  },

  changeIndustry: function (event) {
    //console.log('picker发送选择改变，携带值为', event.detail.value);
    let industry_index = event.detail.value;
    this.setData({
      industry_index: industry_index
    });
    let first_column = this.data.industryArray[industry_index[0]]; // 第一列
    let industry_text = first_column.name;
    let industry_id = first_column.id;
    if (first_column.children.length > 0) {
      let second_column = first_column.children[industry_index[1]]; // 第二列
      if (second_column) {
        industry_text = second_column.name;
        industry_id = second_column.id;
      }
    }
    this.setData({
      industry_text: industry_text,
      'formData.industry_id': industry_id,
    });
  },

  changeIndustryIndex: function (event) {
    //console.log('修改的列为', event.detail.column, '，值为', event.detail.value);
    let data = {
      industries: this.data.industries,
      industry_index: this.data.industry_index
    };
    data.industry_index[event.detail.column] = event.detail.value;
    switch (event.detail.column) {
      case 0:
        data.industries[1] = this.data.industryArray[event.detail.value].children;
        data.industry_index[1] = 0;
        break;
      case 1:
        break;
    }
    this.setData(data);
  },

  getPhoneNumber: function (e) {
    let phone = this.data.formData.phone;
    if (!phone) {
      wx.login({
        success: res => {
          util.getPhoneNumber(e, res.code, api.GetOnlyPhoneUrl).then(response => {
            if (response) {
              wx.showToast({ title: '手机号授权成功', icon: 'none', duration: 1500 });
              this.setData({
                'formData.phone': response,
                is_bind_phone: true
              });
            }
          });
        }
      })
    }
  },

  /**
   * 地理位置
   * @param e
   */
  getChooseLocation: function (e) {
    let that = this;
    wx.getSetting({
      success(res) {
        if (!res.authSetting['scope.userLocation']) {
          wx.authorize({
            scope: 'scope.userLocation',
            success(res) {
              console.log(res);
              that.setChooseLocation()
            },
            fail(res) {
              console.log(res);
              wx.showModal({
                title: '是否授权当前位置',
                content: '需要获取您的地理位置，请确认授权，否则地图功能将无法使用',
                success: function (res) {
                  if (res.confirm) {
                    that.isOpenSetting();
                  }
                }
              })
            }
          })
        } else {
          that.setChooseLocation()
        }
      },
      fail(res) {
        that.isOpenSetting();
      }
    })

  },

  isOpenSetting: function () {
    let that = this;
    wx.openSetting({
      success: (res) => {
        if (res.authSetting['scope.userLocation']) {
          wx.removeStorageSync('userLocationStatus');
          that.setChooseLocation();
        }
      }
    })
  },


  setChooseLocation: function () {
    wx.chooseLocation({
      success: (res) => {
        console.log(res);
        // let address_title = this.data.formData.address_title ? this.data.formData.address_title : res.address;
        this.setData({
          'formData.longitude': res.longitude,
          'formData.latitude': res.latitude,
          'formData.address_title': res.address,
          'formData.address_name': res.name,
        });

        // 根据经纬度获取省份信息
        this.getLocationDetail(res.latitude, res.longitude);
      },
      fail: (res) => {
        console.log(res)
      }
    });
  },
  getLocationDetail: function (latitude, longitude) {
    util.get(api.GetMapKeyUrl).then(res => {
      let key = res.data.data.mapApiKey;


      // 实例化API核心类
      qqmapsdk = new QQMapWX({
        key: key
      });
      qqmapsdk.reverseGeocoder({
        location: {
          latitude: latitude,
          longitude: longitude
        },
        success: (res) => {
          console.log(res);
          this.setData({
            'formData.province': res.result.address_component.province,
            'formData.city': res.result.address_component.city
          })
        },
        fail: function (res) {
          console.log(res)
        }
      })
    })
  },

  formSubmit: function (event) {
    this.setData({
      'formData.address_title': event.detail.value.address_title,
      'formData.company_name': event.detail.value.company_name,
      'formData.email': event.detail.value.email,
      'formData.introduction': event.detail.value.introduction,
      'formData.name': event.detail.value.name,
      'formData.phone': event.detail.value.phone,
      'formData.position': event.detail.value.position,
      'formData.wechat': event.detail.value.wechat,
      'formData.avatar': this.data.formData.avatar ? this.data.formData.avatar : this.data.userInfo.avatar,
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
    if (this.data.formData.cid && this.data.formData.cid > 0) {
      wx.showModal({
        title: '提交信息提示',
        content: '您绑定企业名片成功后, 您名片的公司名称与地址将改为绑定公司的公司名称和地址，请确认继续绑定。',
        success: (res) =>{
          if (res.confirm) {
            this.realSubmit(this.data.formData);
          }
        }
      })
    } else {
      this.realSubmit(this.data.formData);
    }


  },

  realSubmit: function (_formData) {
    wx.showLoading({
      title: '添加中',
    });
    util.post(api.AddUserCarteUrl, _formData).then((res) => {
      wx.hideLoading();
      wx.showToast({
        title: '添加成功',
        icon: 'success',
        duration: 2000,
        success: function () {
          setTimeout(() => {
            wx.navigateBack({
              delta: 1
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
  switchChange(event) {
    this.setData({
      'formData.open': !this.data.is_open,
      is_open: !this.data.is_open
    })
  },
  changeOpen: function (event) {
    let open = 2;
    if (event.detail.value === true) {
      open = 1
    };
    this.setData({
      'formData.open': open,
      is_open: open === 1,
    })
  },
  addTag: function () {
    let tags = this.data.formData.tags;
    if (tags.length === 9) {
      return;
    }
    tags.push('');
    this.setData({
      'formData.tags': tags
    });
  },

  deleteTag: function (event) {
    let index = event.currentTarget.dataset.index;
    if (index > 0) {
      this.data.formData.tags.splice(index, 1);
      this.setData({
        'formData.tags': this.data.formData.tags
      })
    }
  },
  changeTag: function (event) {
    let value = event.detail.value;
    let index = event.currentTarget.dataset.index;
    let tags = this.data.formData.tags;
    tags[index] = value;
    this.setData({
      'formData.tags': tags
    });

  },

  bindingCard: function () {
    wx.setStorageSync('BINDINGCARD', true);
    wx.navigateTo({
      url: '/pages/my/card/bindingCard/index',
    })
  },
  takePhoto: function () {
    wx.chooseImage({
      count: 1,
      sizeType: ['original', 'compressed'],
      sourceType: ['album', 'camera'],
      success: (res) => {
        wx.showLoading({
          title: '解析中...',
        });
        let code_img = res.tempFilePaths[0];
        wx.uploadFile({
          url: api.ScanCardUrl,
          filePath: code_img,
          name: 'file',
          header: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            Authorization: 'Bearer ' + wx.getStorageSync('token')
          },
          success: (res) => {
            wx.hideLoading();
            console.log(res);
            if (res.statusCode !== 200) {
              let data = JSON.parse(res.data);
              wx.showToast({
                title: data.message,
                icon: 'none',
                duration: 2000
              });
              return;
            }
            let data = JSON.parse(res.data);
            let response = data.data;
            this.setData({
              'formData.name': response.name,
              'formData.company_name': response.company_name,
              'formData.phone': response.phone,
              'formData.email': response.email,
              'formData.position': response.position,
              'formData.address_title': response.address_title,
              'formData.province': response.province,
              // 扫描名片后
              'formData.longitude': response.longitude,
              'formData.latitude': response.latitude,
              'formData.city': response.city,
            });
          },
          fail: function (error) {
            wx.hideLoading();
            console.log(error);
            wx.showToast({
              title: '解析失败',
              icon: 'none',
              duration: 2000
            })
          }
        })
      },
      fail: function (error) {
        console.log(error);
      }
    })
  },

 
  changeAddressTitle: function (event) {
    let address_title = event.detail.value;
    this.setData({
      'formData.address_title': address_title
    });

  },

  navigateToUrl(event) {
    let url = event.currentTarget.dataset.url;
    if (url && url !== '#') {
      wx.navigateTo({
        url: url
      });
    }
  },

  onUnload() {
    wx.removeStorageSync('otherToUrl');
  }

})