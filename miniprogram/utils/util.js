var api = require('../config/api.js');
var env = require('../config/env.js');
var QQMapWX = require('../qqmap/qqmap-wx-jssdk.js');
var qqmapsdk = new QQMapWX({
    key: 'NW6BZ-ESAWU-ZGYVX-44WIN-EWY7E-A3FB7'
});

const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatDate = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()

  return [year, month, day].map(formatNumber).join('-');
}

const formatDateTime = date => {
  const hour = date.getHours();
  const minute = date.getMinutes();
  return [hour, minute].map(formatNumber).join(':');
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}

const  getHomeUrl = () => {
  let _homeUrl = "/pages/index/index";
  if (wx.getStorageSync('HOMEURL')) {
    _homeUrl = wx.getStorageSync('HOMEURL');
  }
  return _homeUrl;
}

const setTalk = (_cid) => {
  post(api.SetTalkUrl, { cid: _cid }).then(response => { });
}


/**
 * 封封微信的的request
 */
function request(url, data = {}, method = "GET", is_loading = true) {
  if (is_loading === true) {
    wx.showLoading({
      title: '加载中',
      mask: true
    });
  }
  return new Promise(function(resolve, reject) {
    wx.request({
      url: url,
      data: data,
      method: method,
      header: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + wx.getStorageSync('token')
      },
      success: function(res) {
        console.log("success");
        wx.hideLoading();
        switch (res.statusCode) {
          case 422:
            {
              let data = res.data.errors;
              let content = '';
              Object.keys(data).map(function(key) {
                if (content == '') {
                  let value = data[key];
                  content = value[0];
                }
              });
              wx.showToast({
                title: content,
                icon: 'none',
                duration: 3000
              });
              break;
            }
          case 404:
            {
              wx.showToast({
                icon: 'none',
                title: res.data.message || '页面不存在！',
                duration: 3000
              });
              setTimeout(function() {
                wx.navigateBack({
                  delta: 1
                })
              }, 3000);
              break;
            }
          case 403:
            {
              wx.showToast({
                icon: 'none',
                title: res.data.message || '您没有此操作权限！',
                duration: 3000
              });
              break;
            }
          case 401:
            {
              // 未登录获取当前页面地址，存入缓存
              // 对于一个页面请求了多次需要授权的接口，则会多次经过这里，造成频繁跳转
              let _currentUrl = getCurrentPageUrlWithArgs(1);
              if (_currentUrl.indexOf('passport') < 0) {
                let _lastUrl = getLastUrl();
                if (_currentUrl != _lastUrl) {
                  if (_currentUrl && _currentUrl != 'undefined') {
                    setLastUrl(_currentUrl);
                  }
                  wx.redirectTo({
                    url: '/pages/passport/login/index'
                  });
                }
              }
              break;
            }
          case 500:
          case 501:
          case 503:
            wx.showToast({
              icon: 'none',
              title: '服务器出了点小问题,请联系客服！',
              duration: 2000
            });
            break;
          case 200:
          case 201:
            resolve(res);
            break;
        }

      },
      fail: function(err) {
        wx.hideLoading();
        reject(err);
        wx.showToast({
          icon: 'none',
          title: '服务器出了点小问题,请联系客服！',
          duration: 2000
        });
      },
      complete: function() {

      }
    })
  });
}

function get(url, data = {}, is_loading = true) {
  return request(url, data, 'GET', is_loading)
}

function post(url, data = {}, is_loading = true) {
  return request(url, data, 'POST', is_loading)
}




const fliesUpload = () => {
  return new Promise(function(resolve, reject) {
    wx.chooseImage({
      success: function(res) {
        //缓存下
        wx.showToast({
          title: '正在上传...',
          icon: 'loading',
          success: function(res) {

          }
        });

        wx.uploadFile({
          url: api.FilesUpload,
          filePath: res.tempFilePaths[0],
          header: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            Authorization: 'Bearer ' + wx.getStorageSync('token')
          },
          name: 'file',
          success: function(res) {
            switch (res.statusCode) {
              case 422:
                {
                  let data = res.response.data.errors
                  let content = ''

                  Object.keys(data).map(function(key) {
                    let value = data[key]
                    content = value[0]
                  })

                  wx.showToast({
                    title: content,
                    duration: 2000
                  });
                  break
                }
              case 403:
                {
                  wx.showToast({
                    icon: 'none',
                    title: res.response.data.message || '您没有此操作权限！',
                    duration: 2000
                  });
                  break
                }
              case 406:
                {
                  console.log(res)
                  wx.showToast({
                    title: '图片不合法，请上传合法合规的图片',
                    icon: 'none',
                    duration: 2500
                  });
                  break
                }
              case 401:
                {
                  wx.showToast({
                    title: '请先登录',
                    duration: 3000,
                    success: function() {
                      wx.redirectTo({
                        url: '/pages/passport/login/index'
                      })
                    }
                  });

                  break
                }
              case 500:
              case 501:
              case 503:
                wx.showToast({
                  icon: 'none',
                  title: '服务器出了点小问题,请联系客服！',
                  duration: 2000
                });
                break
              case 200:
              case 201:
                resolve(res);
                break;
            }
          },
          fail: function(err) {
            reject(err);
          },
          complete: function() {
            setTimeout(() => {
              wx.hideToast(); //隐藏Toast
            }, 2500);
          }
        });
      }
    })
  });
}


/**
 * 将本地图片文件地址数组变为上传后的在线图片url数组
 * @param array filePaths
 */
const multipartFliesUpload = () => {
  return new Promise(function(resolve, reject) {

    wx.chooseImage({
      success: function(fileData) {
        //缓存下
        wx.showToast({
          title: '正在上传...',
          icon: 'loading',
          success: function(res) {

          }
        });
        // 上传的后端url
        const url = api.FilesUpload;
        // 因为多张图片且数量不定，这里遍历生成一个promiseList
        let promiseList = fileData.tempFilePaths.map((item) => {
          return new Promise(resolve => {
            wx.uploadFile({
              url,
              filePath: item,
              header: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                Authorization: 'Bearer ' + wx.getStorageSync('token')
              },
              name: 'file',
              success: (res) => {
                switch (res.statusCode) {
                  case 422:
                    {
                      let data = res.response.data.errors
                      let content = ''

                      Object.keys(data).map(function(key) {
                        let value = data[key]
                        content = value[0]
                      })

                      wx.showToast({
                        title: content,
                        duration: 2000
                      });
                      break
                    }
                  case 403:
                    {
                      wx.showToast({
                        icon: 'none',
                        title: res.response.data.message || '您没有此操作权限！',
                        duration: 2000
                      });
                      break
                    }
                  case 406:
                    {
                      wx.showToast({
                        icon: 'none',
                        title: '图片不合法，请上传合法合规的图片',
                        duration: 2500
                      });
                      break
                    }
                  case 401:
                    {
                      wx.showToast({
                        title: '请先登录',
                        duration: 3000,
                        success: function() {
                          wx.redirectTo({
                            url: '/pages/passport/login/index'
                          })
                        }
                      });
                      break
                    }
                  case 500:
                  case 501:
                  case 503:
                    wx.showToast({
                      icon: 'none',
                      title: '服务器出了点小问题,请联系客服！',
                      duration: 2000
                    });
                    break
                  case 200:
                  case 201:
                    // const data = JSON.parse(res.data).relative_url;
                    const data = JSON.parse(res.data).url; // 完整地址
                    resolve(data);
                }
               
              },
              fail: function(err) {
                reject(err);
              },
              complete: function() {
                setTimeout(() =>{
                  wx.hideToast(); //隐藏Toast
                }, 2500);
              }
            });
          });
        });

        const result = Promise.all(promiseList).then((res) => {
          // 返回的res是个数据，对应promiseList中请求的结果，顺序与promiseList相同
          // 在这里也就是在线图片的url数组了
          return res;
        }).catch((err) => {
          reject(err);
        });
        resolve(result);
      }
    });

  });
  // 使用Primise.all来执行promiseList
  /*const result = Promise.all(promiseList).then((res) => {
    // 返回的res是个数据，对应promiseList中请求的结果，顺序与promiseList相同
    // 在这里也就是在线图片的url数组了
    return res;
  }).catch((error) => {
    console.log(error);
  });
  return result;*/
};



// 根据id获取对象数组的键（对象数组必须包含 id 这个字段）
const getObjKeyById = (objectArray, id) => {
  for (let key in objectArray) {
    if (objectArray[key]['id'] == id) {

      return key;
      break;
    }
  }
};

const getObjInfoDataByname = (objectArray, name, value) => {
  for (let key in objectArray) {
    if (objectArray[key][name] == value) {
      return objectArray[key];
      break;
    }
  }
};


const getSearchUrlParamUrl = (_paramData) => {
  let _str = '';
  for (let i in _paramData) {
    if (i == 0) {
      _str += '?' + _paramData[i].param + '=' + _paramData[i].value;
    } else {
      _str += '&' + _paramData[i].param + '=' + _paramData[i].value
    }
  }
  return _str;
};

const getUserInfo = () => {
  return new Promise(function(resolve, reject) {
    let _token = wx.getStorageSync('token');
    if (!_token) {
      wx.showModal({
        title: '提示',
        content: '你还没有登录，是否登录',
        success(res) {
          if (res.confirm) {
            wx.navigateTo({
              url: '/pages/passport/login/index'
            });
          } else if (res.cancel) {
            wx.redirectTo({
              url: '/pages/index/index'
            })
          }
        }
      });
      return false;
    }

  });
};


/**
 * 调用微信登录
 */
const wxLogin = () => {
  return new Promise(function(resolve, reject) {
    wx.login({
      success: function(res) {
        if (res.code) {
          resolve(res.code);
        } else {
          reject(res);
        }
      },
      fail: function(err) {
        reject(err);
      }
    });
  });
};

const wxMiNiLogin = ({
  code,
  userInfo
}) => {
  return new Promise(function(resolve, reject) {
    request(api.LoginUrl, {
      code: code,
      type: 'wx_mini',
      userInfo: userInfo
    }, 'POST').then(res => {
      if (res.statusCode === 200) {
        //存储用户信息
        wx.setStorageSync('userInfo', res.data.user_info);
        wx.setStorageSync('token', res.data.token);
        resolve(res);
      } else {
        reject(res);
      }
    }).catch((err) => {
      reject(err);
    });

  });
};

// 从缓存中获取经纬度
const getLocation = () => {
  return new Promise(function(resolve, reject) {
    let location_expiration = wx.getStorageSync("LOCATION_EXPIRATION"); //获取缓存时间
    let timestamp = parseInt(Date.parse(new Date()) / 1000); //获取当前时间
    // 过期
    if (!location_expiration || (location_expiration && location_expiration < timestamp)) {
      wx.getLocation({
        type: 'gcj02', // 返回可以用于wx.openLocation的经纬度
        success: function(res) {
          // 设置缓存时间
          let timestamp = parseInt(Date.parse(new Date()) / 1000);
          let expiration = timestamp + 120; //单位秒，即120秒（两分钟）
          wx.setStorageSync("LOCATION_EXPIRATION", expiration);
          let data = {
            latitude: res.latitude, // 纬度
            longitude: res.longitude, // 经度
          };

          wx.setStorageSync("LOCATION_INFO", JSON.stringify(data));
          resolve(data);
        },
        fail: function(err) {
          reject(err);
        }
      });
      return;
    }
    let location_info = wx.getStorageSync('LOCATION_INFO');
    if (location_info) {
      resolve(JSON.parse(location_info));
    }
  });
};

// 从缓存中获取access_token，缓存两个小时
const getAccessToken = (configInfo) => {
  return new Promise(function(resolve, reject) {
    let access_token_expiration = wx.getStorageSync("ACCESS_TOKEN_EXPIRATION"); //获取缓存时间
    let timestamp = parseInt(Date.parse(new Date()) / 1000); //获取当前时间
    // 过期
    if (!access_token_expiration || (access_token_expiration && access_token_expiration < timestamp)) {
      let appid = configInfo.WECHAT_OFFICIAL_ACCOUNT_APPID;
      let secret = configInfo.WECHAT_OFFICIAL_ACCOUNT_SECRET;
      get(`https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=${appid}&secret=${secret}`).then(res => {
        let access_token = res.data.access_token;
        let expires_in = res.data.expires_in;

        // 设置缓存
        let timestamp = parseInt(Date.parse(new Date()) / 1000);
        let expiration = timestamp + expires_in; // 两个小时
        wx.setStorageSync('ACCESS_TOKEN', access_token);
        wx.setStorageSync('ACCESS_TOKEN_EXPIRATION', expiration);
        resolve(access_token);
      })
      return;
    }
    let access_token = wx.getStorageSync('ACCESS_TOKEN');
    if (access_token) {
      resolve(access_token)
    }
  })
};

// 页面栈缓存1小时
function getLastUrl() {
  let lasturl_expiration = wx.getStorageSync("lasturl_expiration"); //获取缓存时间
  let timestamp = parseInt(Date.parse(new Date()) / 1000); //获取当前时间
  // 过期
  if (!lasturl_expiration || (lasturl_expiration && lasturl_expiration < timestamp)) {
    return '';
  }
  return wx.getStorageSync('lastUrl');
}

// 页面栈缓存1小时
function setLastUrl(lastUrl) {
  // 设置缓存
  let timestamp = parseInt(Date.parse(new Date()) / 1000);
  let expiration = timestamp + 3600; // 1个小时
  wx.setStorageSync('lastUrl', lastUrl);
  wx.setStorageSync("lasturl_expiration", expiration); //获取缓存时间
}

//  复制到剪切板
function getClipboard(e) {
  let _data = e.currentTarget.dataset.value;
  if (!_data) {
    return false;
  }
  wx.setClipboardData({
    data: _data,
    success(res) {
      wx.getClipboardData({
        success(res) {
          console.log(res.data); // data
          return res.data;
        }
      })
    }
  })
}

const setCurrentNavigationBarTitle = (_title) => {
  let _setTitle = '';
  if (_title) {
    _setTitle = _title;
  }
  wx.setNavigationBarTitle({
    title: _setTitle
  })
};

const getPhoneNumber = (e, code, url) => {
  return new Promise(function(resolve) {
    if (e.detail.errMsg === 'getPhoneNumber:ok') {
      let params = {
        iv: e.detail.iv,
        encryptedData: e.detail.encryptedData,
        code: code
      };
      let _url = api.GetPhoneUrl;
      if (url) {
        _url = url;
      }
      request(_url, params, 'POST').then(respond => {
        let _data = respond.data;
        if (_data.phoneNumber) {
          resolve(_data.phoneNumber);
        } else {
          wx.showToast({
            title: '请求手机号码失败，请稍后重试',
            icon: 'none',
            duration: 1000
          });
        }
      });
    } else if (e.detail.errMsg === 'getPhoneNumber:user deny') { //用户点击拒绝
      wx.showToast({
        title: '您点击了拒绝授权，将无法使用部分功能！',
        icon: 'none',
        duration: 1000
      });
    } else if (e.detail.errMsg === 'getPhoneNumber:fail 用户未绑定手机，请先在微信客户端进行绑定后重试') {
      wx.showToast({
        title: '您的微信未绑定手机号',
        icon: 'none',
        duration: 3000
      })
    } else {
      wx.showToast({
        title: '您拒绝了授权，将无法使用部分功能',
        icon: 'none',
      })
    }
  })
};

const subscribeMessage = (tid) => {
  return new Promise(function(resolve, reject) {
    let _tmplId = "jCItW95uAINhEOimxH3l9xeV_3KRuhqFjMdNexzSILA";
    if (tid) {
      _tmplId = tid;
    }
    wx.requestSubscribeMessage({
      tmplIds: [_tmplId],
      success: (res) => {
        if (res[_tmplId] == "accept") {
          resolve(true);
        } else {
          resolve(false);
        }
      }
    })
  })
};

// 获取页面栈及参数
function getCurrentPageUrlWithArgs(num) {
  const pages = getCurrentPages();
  if (pages.length <= 0) {
    return false;
  }
  const currentPage = pages[pages.length - num];
  const url = currentPage.route;
  const options = currentPage.options;
  let urlWithArgs = `/${url}?`;
  for (let key in options) {
    const value = options[key];
    urlWithArgs += `${key}=${value}&`
  }
  urlWithArgs = urlWithArgs.substring(0, urlWithArgs.length - 1);
  return urlWithArgs
}




const getFlatternDistance = (lng1, lat1, lng2, lat2) => {
    var radLat1 = getRad(lat1);
    var radLat2 = getRad(lat2);
    var a = radLat1 - radLat2;
    var b = getRad(lng1) - getRad(lng2);
    var s = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin(a / 2), 2)
        + Math.cos(radLat1) * Math.cos(radLat2)
        * Math.pow(Math.sin(b / 2), 2)));
    s = s * EARTH_RADIUS;
    s = Math.round(s * 10000) / 10000;
    return s;//返回数值单位：公里
}


const getNewLocation = (is_detail = false) => {
    let data = {}
    
    return new Promise((resoleve, reject) => {
        wx.getLocation({
            type: 'gcj02',
            success: res => {
                data.latitude = res.latitude
                data.longitude = res.longitude
                if (is_detail === true) {
                    qqmapsdk.reverseGeocoder({
                        //位置坐标，默认获取当前位置，非必须参数
                        location: {
                            latitude: data.latitude,
                            longitude: data.longitude
                        },
                        success: res => {//成功后的回调
                            data = Object.assign(data, {
                                province: res.result.address_component.province,
                                city: res.result.address_component.city,
                                district: res.result.address_component.district,
                            })
                            resoleve(data)
                        },
                        fail: function (error) {
                            reject(error)
                        },
                    })
                } else {
                    resoleve(data)
                }

            },
            fail: err => {
                reject(err)
            }
        })
    })
}


const scopeLocation = (callback, is_detail = true) => {
    wx.showToast({
        title: '请授权地理位置',
        icon: 'none'
    })
    setTimeout(() => {
        wx.getSetting({
            success: res => {
                !res.authSetting['scope.userLocation'] && wx.showModal({
                    content: '您暂未开启权限，是否开启',
                    confirmColor: '#72bd4a',
                    success: res => {
                        // 用户确认授权后，进入设置列表
                        if (res.confirm) {
                            wx.openSetting({
                                success: () => {
                                    getNewLocation(is_detail).then(res => {

                                        if (callback && typeof callback === 'function') {
                                            callback(res)
                                        }
                                    })
                                },
                                fail: err => {
                                    console.log(err)
                                }
                            })
                        } else {
                            scopeLocation(callback, is_detail)
                        }
                    }
                })
            },
        })
    }, 1500)
}

module.exports = {
  formatTime: formatTime,
  get: get,
  post: post,
  fliesUpload: fliesUpload,
  getObjKeyById: getObjKeyById,
  getObjInfoDataByname: getObjInfoDataByname,
  formatDate: formatDate,
  formatDateTime: formatDateTime,
  multipartFliesUpload: multipartFliesUpload,
  getSearchUrlParamUrl: getSearchUrlParamUrl,


  wxLogin: wxLogin,
  getUserInfo: getUserInfo,
  wxMiNiLogin: wxMiNiLogin,
  getLocation: getLocation,
  getAccessToken: getAccessToken,
  setCurrentNavigationBarTitle: setCurrentNavigationBarTitle,

  getPhoneNumber: getPhoneNumber,
  getLastUrl: getLastUrl,
  getClipboard: getClipboard,

  subscribeMessage: subscribeMessage,
  getCurrentPageUrlWithArgs: getCurrentPageUrlWithArgs,

  getHomeUrl: getHomeUrl,

    getFlatternDistance: getFlatternDistance,
    getNewLocation: getNewLocation,
    scopeLocation: scopeLocation,

  setTalk: setTalk
};
