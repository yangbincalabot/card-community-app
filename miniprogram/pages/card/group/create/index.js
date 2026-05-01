// pages/card/group/create/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({
    /**
     * 页面的初始数据
     */
    data: {
        list:[],
        search: '',
        selected_num: 0,
        selectArr: []
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        let _id = options.id;
        if (_id) {
            that.getDetail(_id);
            that.getList(_id);
        } else {
            that.getList();
        }
    },

    getDetail: function (_id) {
        let that = this;
        util.post(api.GroupShowUrl, {id: _id})
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                if (!_data) {
                    that.prompt();
                    return false;
                }
                that.setData({
                    id: _id,
                    selected_num: _data.num,
                })
            });
    },

    getList: function (_id) {
        let that = this;
        let param = {};
        param.search = that.data.search;
        if (_id) {
            param.gid = _id;
        }
        util.post(api.GroupCreateListUrl, param)
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                that.setData({
                    list: _data
                });
                that.checkData(_data);
            });
    },

    // 组装提交数组
    checkData: function (_data) {
        let that = this;
        let currentArr = [];
        if (_data && _data.length > 0) {
          for (let index in _data) {
                let arr = _data[index].datas;
                for (let key in arr) {
                    let item = arr[key];
                    if (item.selected) {
                        currentArr.push(item.id);
                    }
                }
            }
        }
        console.log(currentArr);
        setTimeout(() => {
            that.setData({
                selectArr: currentArr
            })
        },500);
    },

    prompt: function () {
        wx.showToast({ title: '页面不存在', icon: 'none', duration: 1000 });
        setTimeout(function () {
            wx.navigateBack({
                delta: 1
            })
        }, 800);
    },

    changeSelected: function (e) {
        let that = this;
        let _index = e.currentTarget.dataset.index;
        let _son_index = e.currentTarget.dataset.son_index;
        let _status = e.currentTarget.dataset.status;
        let _info_id = e.currentTarget.dataset.info_id;
        let _selected_num = that.data.selected_num;
        let _list = that.data.list;
        let _selectArr = that.data.selectArr;
        _list[_index].datas[_son_index].selected = !_list[_index].datas[_son_index].selected;
        console.log(_status);
        if (_status) {
            _selected_num--;
            _selectArr = that.selectArrSplice(_selectArr, _info_id);
        } else {
            _selected_num++;
            _selectArr.push(_info_id)
        }
        that.setData({
            list: _list,
            selected_num: _selected_num,
            selectArr: _selectArr
        })
    },

    selectArrSplice: function (_selectArr, _info_id) {
        if (!_selectArr || !_info_id) {
            return [];
        }
        for (let i = 0; i < _selectArr.length; i++) {
            if(_selectArr[i] == _info_id) {
                _selectArr.splice(i, 1);
                break;
            }
        }
        return _selectArr;
    },

    toSubmit: function () {
        let that = this;
        let _selected_num = that.data.selected_num;
        if (_selected_num < 1) {
            wx.showToast({ title: '请至少选择一个用户', icon: 'none', duration: 1000 });
            return false;
        }
        that.setData({
            is_submit: true
        });
        let _id = that.data.id;
        let _selectArr = that.data.selectArr;
        if (that.data.id) {
            that.backToOperating(_selectArr);
            return false;
        }
        let _param = {};
        _param.selectArr = _selectArr;
        console.log(_param);
        util.post(api.GroupCreateUrl, _param)
            .then(response => {
                let _data = response.data.data;
                wx.showToast({title: '创建成功', icon: 'none', duration: 800});
                setTimeout(function () {
                    wx.navigateBack({
                        delta: 1
                    })
                }, 700);
            });
        setTimeout(function () {
            that.setData({
                is_submit: false
            })
        }, 2000);
    },

    backToOperating: function (_selectArr) {
        let that = this;
        if (_selectArr && _selectArr.length > 0) {
            wx.setStorageSync('groupTemporarilyArr', _selectArr);
        }
        setTimeout(() => {
            wx.navigateBack({
                delta: 1
            })
        },100);
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

    // 页面跳转
    navigateToUrl: function (event) {
        let url = event.currentTarget.dataset.url;
        if(url && url !== '#'){
            wx.navigateTo({
                url: url
            });
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