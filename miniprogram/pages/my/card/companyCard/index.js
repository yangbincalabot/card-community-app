const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";
Page({

    /**
     * 页面的初始数据
     */
    data: {
      navBgColor:'rgba(255,255,255,0)',
      navColor:'#fff',
        companyCardStatus:'',
        mask: false,
        array: [],
        bott: '',
        userInfo : {},
        formData: {
            company_name: '',
            logo: '',
            contact_number: '',
            industry_id: '',
            introduction: '',
            website: '',
            images: [''],
            longitude: '',
            latitude: '',
            address_title: '',
            address_name: '',
            _method: 'PUT',
        },
        industries: [[],[]], // 行业数据，picker组件显示
        industry_text: '', // 行业名称
        industryArray: [],// 所有行业数据
        industry_index: [0,0], // 默认行业选择索引
        input_address_title: '',
        logo_url: '',
        hidden: true,
        business_cost: 0, // 开通企业会员费用
        loading: false,
        cash_password: '', // 支付密码
    },
    lookmp(){
      wx.navigateTo({
        url: '../../../card/companyDetail/index?id='+this.data.userInfo.company_card.id,
      })
    },
  onPageScroll: function (e) {
    let _this = this;
    console.log(e.scrollTop)
    if (e.scrollTop >= 140) {
      // 动态设置title
      _this.setData({
        navBgColor: 'rgba(255,255,255,1)',

      })
    } else if (e.scrollTop >= 70) {
      _this.setData({
        navBgColor: 'rgba(255,255,255,0.5)',

      })
    } else {
      _this.setData({
        navBgColor: 'rgba(255,255,255,0)',

      })
    }
  },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        // this.setData({
        //   navHeight: App.globalData.navHeight,
        //   navTop: App.globalData.navTop,
        // })
        this.initValidate();
        wx.showLoading({
            title: '加载中',
            mask: true
        });
        this.getIndustries();
        this.getBusinessCost();
        this.getUserInfo();
        // this.setData({
        //   companyCardStatus: options.card
        // })
    },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },
  getUserInfo: function() {
    util.get(api.GetCompanyCardInfoUrl).then(res => {
      let user_info = res.data.data;
      let formData = user_info.company_card;
      let logo_url = user_info.avatar;
      if (formData) {
        let industry_text = '请选择所在行业';
        let industry_index = this.data.industry_index;
        let industries = this.data.industries;
        logo_url = formData.logo;
        if (formData.industry) {
          industry_text = formData.industry.name;

          let industryArray = this.data.industryArray;
          // 设置默认索引
          if (industryArray.length > 0) {
            if (formData.industry.parent_id > 0) {
              // 有上下级的情况下
              for (let index in industryArray) {
                if (industryArray[index].id === formData.industry.parent_id) {
                  industry_index[0] = index;
                  // 遍历二级数据
                  for (let i in industryArray[index].children) {
                    if (industryArray[index].children[i].id === formData.industry.id) {
                      industry_index[1] = i;
                      break;
                    }
                  }
                  industries[1] = industryArray[index].children; // 设置二级数据
                  break;
                }
              }

            } else {
              // 只有一级的情况下
              for (let index in industryArray) {
                if (industryArray[index].id === formData.industry.id) {
                  industry_index[0] = index;
                  industries[1] = []; // 清空二级数据
                  break;
                }
              }
            }
          }
        }
        this.setData({
          'formData.company_name': formData.company_name,
          'formData.logo': formData.logo ? formData.logo : user_info.avatar,
          'formData.contact_number': formData.contact_number,
          'formData.industry_id': formData.industry_id,
          'formData.introduction': formData.introduction,
          'formData.website': formData.website,
          'formData.images': formData.images,
          'formData.longitude': formData.longitude,
          'formData.latitude': formData.latitude,
          'formData.address_title': formData.address_title,
          'formData.address_name': formData.address_name,
          'industry_text': industry_text,
          industry_index: industry_index,
          industries: industries,
        });
      }
      this.setData({
        companyCardStatus: user_info.companyCardStatus,
        userInfo: user_info,
        logo_url: logo_url,
        hidden: false
      });
      wx.hideLoading();
    });
  },

  initValidate() {
    let rules = {
      logo: {
        required: true,
      },
      company_name: {
        required: true,
        maxlength: 20
      },
      industry_id: {
        min: 1,
      },
      address_title: {
        required: true,
        maxLength: 255,
      },
      contact_number: {
        required: true,
        //telephone: true
      },
      website: {
        required: true,
        maxLength: 255,
      },
      introduction: {
        required: true,
      }

    };

    let message = {
      logo: {
        required: '请上传公司logo',
      },
      company_name: {
        required: '请输入公司名称',
        maxlength: '公司名称过长'
      },
      industry_id: {
        min: "请选择行业",
      },
      address_title: {
        required: "请输入公司地址",
        maxLength: '公司地址过长'
      },
      contact_number: {
        required: "请输入手机号码",
        // telephone: '手机号码格式不正确',
      },
      website: {
        required: '请输入企业官网',
        maxlength: '企业官网过长'
      },
      introduction: {
        required: '请输入企业介绍',
      },
    };
    //实例化当前的验证规则和提示消息
    this.WxValidate = new WxValidate(rules, message);
  },

  getPassword: function(e) {
    let value = e.currentTarget.dataset.value;
    if (this.data.array.length < 6) {
      this.data.array.push(value)
    }
    if (this.data.array.length == 6) {
      // 提交支付
      let cash_password = this.data.array.join('');
      this.setData({
        cash_password: cash_password
      });
      this.data.mask = false;
      this.data.array = [];
      this.data.bott = '';

      this.toWechatPayment();
    }
    this.setData({
      mask: this.data.mask,
      array: this.data.array,
      bott: this.data.bott
    });
  },

  // 发起微信支付到后台
  toWechatPayment: function() {

    wx.showLoading({
      title: '提交中',
    });
    util.post(api.CompanyCardMiNiPayUrl).then(res => {
      let response = res.data.data;
      if (response.appId) {
        this.weChatMiNiPay(response);
      } else {
        wx.redirectTo({
          url: '../paySuccess/index',
        });
      }
    });
  },

  // 微信支付
  weChatMiNiPay: function(charge) {
    wx.requestPayment({
      "timeStamp": charge.timeStamp,
      "nonceStr": charge.nonceStr,
      "package": charge.package,
      "signType": charge.signType,
      "paySign": charge.paySign,
      success: res => {
        if (res.errMsg == 'requestPayment:ok') {
          wx.redirectTo({
            url: '../paySuccess/index',
          })

        } else {
          wx.showModal({
            content: '调用支付失败！',
            showCancel: false
          })
        }
      },
      fail: err => {
        console.log(err);
        if (err.errMsg == 'requestPayment:fail cancel') {
          wx.redirectTo({
            url: '/pages/my/card/companyCard/index'
          })
        } else {
          wx.showModal({
            content: '调用支付失败！',
            showCancel: false
          })
        }
      }
    })
  },
  reset: function() {
    this.data.array = []
    this.setData({
      array: this.data.array
    })
  },
  backspace: function() {
    this.data.array.pop()
    this.setData({
      array: this.data.array
    })
  },

  // 弹出支付密码框
  masks: function() {
    let that = this;
    this.data.mask = true
    setTimeout(function() {
      that.data.bott = 'bot'
    }, 50)
    this.setData({
      mask: this.data.mask,
      bott: that.data.bott
    })
  },
  maskss: function() {
    this.data.mask = false
    this.data.bott = ''
    this.data.array = []
    this.setData({
      mask: this.data.mask,
      bott: this.data.bott,
      array: this.data.array
    })
  },

  getIndustries: function() {
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
        industries: industries
      });
    })
  },
  changeIndustry: function(event) {
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

  changeIndustryIndex: function(event) {
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

  /**
   * 地理位置
   * @param e
   */
  getChooseLocation: function(e) {
    let that = this;
    wx.getSetting({
      success(res) {
        if (!res.authSetting['scope.userLocation']) {
          wx.authorize({
            scope: 'scope.userLocation',
            success(res) {
              that.setChooseLocation()
            },
            fail(res) {
              console.log(res);
              wx.showModal({
                title: '是否授权当前位置',
                content: '需要获取您的地理位置，请确认授权，否则地图功能将无法使用',
                success: function(res) {
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

  isOpenSetting: function() {
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


  setChooseLocation: function() {
    wx.chooseLocation({
      success: (res) => {
        console.log(res);
        this.setData({
          'formData.longitude': res.longitude,
          'formData.latitude': res.latitude,
          'formData.address_title': res.address,
          'formData.address_name': res.name,
        });
      },
      fail: (res) => {
        console.log(res)
      }
    });
  },

  UploadImage: function(event) {
    let type = '';
    if (event.currentTarget.dataset.type) {
      type = event.currentTarget.dataset.type;
    }
    switch (type) {
      case 'logo':
        // logo上传
        util.fliesUpload().then((respond) => {
          let uploadResponse = JSON.parse(respond.data);
          // logo上传
          this.setData({
            'formData.logo': uploadResponse.storage_path,
            'logo_url': api.ResourceRootUrl + uploadResponse.relative_url,
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

  deleteImage: function(event) {
    let index = event.currentTarget.dataset.index;
    if (index >= 0) {
      this.data.formData.images.splice(index, 1);
      this.setData({
        'formData.images': this.data.formData.images
      })
    }
  },

  formSubmit: function(event) {
    if (this.data.userInfo.companyCardStatus !== true) {
      wx.showToast({
        title: '请升级企业会员',
        icon: 'none',
        duration: 2000,
      });
      return false
    }
    this.setData({
      'formData.address_title': event.detail.value.address_title,
      'formData.company_name': event.detail.value.company_name,
      'formData.contact_number': event.detail.value.contact_number,
      'formData.introduction': event.detail.value.introduction,
      'formData.website': event.detail.value.website,
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
    wx.showLoading({
      title: '编辑中',
    });
    util.post(api.UpdateCompanyCardUrl, this.data.formData).then((res) => {
      wx.hideLoading();
      wx.showToast({
        title: '编辑成功',
        icon: 'success',
        duration: 2000,
        success: () => {
          wx.setStorageSync("PERFECT_BUSINESS_CARD", this.data.formData.company_name);
          setTimeout(() => {
            // wx.redirectTo({
            //     url: '../../index/index'
            // })
            wx.navigateBack({
              delta: 1
            })
          }, 2000)
        }
      });
    })
  },

  getBusinessCost: function() {
    util.get(api.GetBusinessCostUrl).then((res) => {
      this.setData({
        business_cost: res.data.data.businessCost
      });
    });
  },

  navigatorToUrl: function(e) {
    let _url = e.currentTarget.dataset.url;
    let _type = e.currentTarget.dataset.type;
    if (_type) {
      // tab页面
      wx.redirectTo({
        url: _url
      })
    } else {
      wx.redirectTo({
        url: _url
      });
    }
  },

  navigatoBack() {
    wx.navigateBack({
      delta: 1
    })
  }
})