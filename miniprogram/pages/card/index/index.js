// pages/card/index/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
var storageIndex='';
var storagesonIndex=''
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isHidden: true,
    menu_show:false,
    startY:'',
    start_index:'',
    showAlpha:0,
    list:[],
    otherDetail:{},
    alpha: '',
    windowHeight: '',
    addBg: false,
    search: '',
    starsImg: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/%E6%98%9F1%402x%20(1).png',
    num: 0,
    showMask:false,
    showBtn:false,
    movableLeft:''
  },
  bind_tabbar(e) {
    var _this = this;
    let index = e.currentTarget.dataset.index;
    switch (index) {
      case 'one':
        wx.navigateTo({
          url: '/pages/card/special/index',
        });
        break;
      case 'two':
        wx.navigateTo({
          url: '/pages/card/contact/index',
        });
        break;
      case 'three':
        _this.setData({
          menu_show: !_this.data.menu_show,
          showMask:!_this.data.showMask
        });
        break;
      case 'four':
        wx.navigateTo({
            url: '/pages/card/map/map',
        });
        break;
      case 'five':
        wx.navigateTo({
          url: '/pages/card/talk/index',
        });
        break;
    }
  },


  handleMovableChange: function (e) {
    var _this=this
    if (e.detail.source === 'friction') {
      // console.log(e.detail.x)
      if (e.detail.x < -60) {
        _this.showDeleteButton(e)
      } else {
        _this.hideDeleteButton(e)
      }
    } else if (e.detail.source === 'out-of-bounds' && e.detail.x === 0) {
      _this.hideDeleteButton(e)
    }
  },

  handleTouchStart(e) {
    this.startX = e.touches[0].pageX
  },

  handleTouchEnd(e) {
    var _this=this
    console.log(storageIndex)
    console.log(storagesonIndex)
    if(storageIndex!==''&&storagesonIndex!==''){
      let list_str = 'list[' + storageIndex + '].datas[' + storagesonIndex + '].xmove';
      _this.setData({
        [list_str]: 0
      })
      storageIndex=e.currentTarget.dataset.index
      storagesonIndex=e.currentTarget.dataset.sonIndex
      console.log("000")
    }else{
      storageIndex=e.currentTarget.dataset.index
      storagesonIndex=e.currentTarget.dataset.sonIndex
      console.log("111")
    }

    if (e.changedTouches[0].pageX < this.startX && e.changedTouches[0].pageX - this.startX <= -30) {
      _this.showDeleteButton(e)
    } else if (e.changedTouches[0].pageX > this.startX && e.changedTouches[0].pageX - this.startX < 30) {
      _this.showDeleteButton(e)
    } else {
      _this.hideDeleteButton(e)
    }
  },
  onPageScroll(){
    var _this=this
    // this.setXmove(_index, _sonIndex, 0)
    console.log(_this.data.list)
    for(var i=0;i<_this.data.list.length;i++){
      for(var j=0;j<_this.data.list[i].datas.length;j++){
          let list_str = 'list[' + i + '].datas[' + j + '].xmove';
          _this.setData({
            [list_str]: 0
          })
      }
      
    }
  },
  /**
   * 显示删除按钮
   */
  showDeleteButton: function (e) {
    let _index = e.currentTarget.dataset.index;
    let _sonIndex = e.currentTarget.dataset.sonIndex;
    this.setXmove(_index, _sonIndex, -130)
  },
  /**
   * 隐藏删除按钮
   */
  hideDeleteButton: function (e) {
    let _index = e.currentTarget.dataset.index;
    let _sonIndex = e.currentTarget.dataset.sonIndex;
    this.setXmove(_index, _sonIndex, 0)
  },

  /**
     * 设置movable-view位移
     */
  setXmove: function (_index, _sonIndex, xmove) {
    console.log(xmove)
    let _str = 'list[' + _index + '].datas[' + _sonIndex + '].xmove';
    this.setData({
      [_str]: xmove
    })
  },

  deleteBtn(e){
    let _cid = e.currentTarget.dataset.cid;
    util.post(api.AttentionStoreUrl, { from_id: _cid })
      .then(response => {
        //  提示信息
        wx.showToast({
          title: '操作成功',
          icon: 'none',
          duration: 1500
        });
        setTimeout(() => {
          this.getList();
        }, 1500)
      });
  },

  collectBtn(e){
    let _index = e.currentTarget.dataset.index;
    let _sonIndex = e.currentTarget.dataset.sonIndex;
    let _cid = e.currentTarget.dataset.cid;
    util.post(api.SetSpecialUrl, { cid: _cid })
      .then(response => {
        wx.showToast({
          title: '操作成功',
          icon: 'none',
          duration: 1000
        })
        let _list = this.data.list;
        let _str = 'list[' + _index + '].datas[' + _sonIndex + '].special';
        let _currectValue = !_list[_index].datas[_sonIndex].special;
        this.setData({
          [_str]: _currectValue
        })
        this.setXmove(_index, _sonIndex, 0)
      });

  },
  
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  getOtherDetail: function () {
    let that = this;
    util.post(api.CardOtherDetail, {})
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          if (_data) {
            that.setData({
              otherDetail: _data,
              user: _data.user,
              isHidden: false,
            })
          }
        });
  },

  getList: function () {
    let that = this;
    let _search = that.data.search;
    util.post(api.CardIndex, {search: _search})
        .then(response => {
          let _data = response.data.data.list;
          let _num = response.data.data.num;
          let _list=[]
          
          for(let i in _data){
            _list.push(_data[i])
          }
          that.setData({
            list: _list,
            num: _num
          })
        });
  },

  // 页面跳转
  navigateToUrl: function (event) {
    let url = event.currentTarget.dataset.url;
    let _carte_id = this.data.otherDetail.carte_id;
      let society_name = event.currentTarget.dataset.society_name;
    if (!_carte_id) {
      wx.showToast({ title: '请前往个人中心创建名片', icon: 'none', duration: 800 });
      return false;
    }
    if(url && url !== '#'){
        if (society_name){
            wx.navigateTo({
                url: url,
                success: res => {
                    res.eventChannel.emit('society', { society_name })
                }
            });
        }else{
            wx.navigateTo({
                url: url,
            });
        }
      
    }
  },

  playPhone: function (e) {
    let that = this;
    let _phone = e.currentTarget.dataset.phone;
    let _cid = e.currentTarget.dataset.cid;
    if (!_phone) {
      wx.showToast({ title: '该用户未设置设置电话', icon: 'none', duration: 800 });
      return false;
    }
    if (_cid) {
      util.setTalk(_cid);
    }
    wx.makePhoneCall({
      phoneNumber: _phone
    })
  },


  addPhone: function (e) {
    let _item = e.currentTarget.dataset.item;
    console.log(_item)
    let _post = {};
    if (_item.name) {
      _post.firstName = _item.name;
    }
    if (_item.phone) {
      _post.mobilePhoneNumber = _item.phone;
    }
    if (_item.company_name) {
      _post.organization = _item.company_name;
    }
    if (_item.position) {
      _post.title = _item.position;
    }
    if (_item.email) {
      _post.email = _item.email;
    }
    console.log(_post)
    // 添加到手机通讯录
    wx.addPhoneContact(_post)
  },



  //点击
  handlerAlphaTap(e) {
    let { ap } = e.target.dataset;
    let _index = e.target.dataset.index
    let startY = e.touches[0].pageY
    this.setData({ 
      alpha: ap,
      addBg:true,
      showAlpha:_index*16+8,
      start_index:_index,
      startY: startY
     });
  },
  //滑动
  handlerMove(e) {
    let list = this.data.list;
    console.log(e)
    // this.setData({ addBg: true });
    let rY = e.touches[0].pageY-this.data.startY;//竖向滑动的距离
    console.log(rY)
    
    let start_index = this.data.start_index
    let __index = start_index + Math.round(rY/16)  
    console.log(__index)
    this.setData({
      alpha: list[__index].alphabet,
    })
    this.setData({
      showAlpha: __index*16+8,
      
    })
    
    // if (rY >= 0) {
    //   let index = Math.ceil((rY - this.apHeight) / this.apHeight);
    //   if (0 <= index < list.length) {
    //     let nonwAp = list[index];
        
    //   }
    // }
  },
  //滑动结束
  handlerEnd(e) {
    this.setData({ addBg: false });
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    try {
      var res = wx.getSystemInfoSync();
      //每一个字母所占的高度
      this.apHeight = res.windowHeight / 26;
      this.setData({ windowHeight: res.windowHeight })
    } catch (e) {

    }
  },

  changeSearch: function (e) {
    let that = this;
    let _value = e.detail.value;
    that.setData({
      search: _value
    });
  },

  searchBtn: function (e) {
    let that = this;
    that.getList();
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this;
    that.getList();
    that.getOtherDetail();
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

  }
});