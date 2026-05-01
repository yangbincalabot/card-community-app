const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
      swithc_val:true,
      navBgColor: 'rgba(255,255,255,0)',
      navColor: '#fff',
        current_page: 1, // 当前页数
        last_page: 1, // 最后一页
        next_page_url: '', // 下一页链接
        companies: [],
        keyword: '', // 搜索关键字,
        sort: '', // 排序方式, latest-最新， nearby-附近,
        latitude: '', // 纬度
        longitude: '',  // 经度,
        attestation: '', // 认证,

        industries: [[],[]], // 行业数据，picker组件显示
        industryArray: [],// 所有行业数据
        industry_index: [0,0], // 默认行业选择索引,
        industry_text: '', // 行业名称
        industry_id: 0, // 选中的行业id

        provinces: [], // 所有省份
        select_province: '', // 选择的省份
        select_city: '', // 选择的城市
        province_index: 0,
        city_index: 0,
        areas: [[], []], // 省市数据，picker组件显示
        areasArray: [], // 所有省市

        select_tab_index: -1, // 选中

        is_show: false,
        is_login: false,
        params : {}, // 地址搜索条件
    },
  toSetting(){
    wx.navigateTo({
        url: '../setting_two/index',
        success: res => {
            res.eventChannel.on('custom-search', params => {
                this.setData({
                    swithc_val: false,
                    params: params,
                    next_page_url: api.getCardSquareListUrl,
                    current_page: 1,
                    last_page: 1,
                    companies: [],
                });
                this.getCompanies();
            })
        }
    })
  },
  onPageScroll: function (e) {
    let _this = this;
    console.log(e.scrollTop)
    if (e.scrollTop >= 160) {
      // 动态设置title
      _this.setData({
        navBgColor: 'rgba(255,255,255,1)',

      })
    } else if (e.scrollTop >= 90) {
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
        wx.showLoading({
            title: '加载中',
        });
        this.setData({
            next_page_url: api.getCardSquareListUrl,
        });
        this.getIndustries();
        this.getProvinces();
        this.getCompanies();
        this.getAreas();
        this.checkLogin();

        console.log(options);
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
        this.setData({
            next_page_url: api.getCardSquareListUrl,
            current_page: 1,
            select_tab_index: -1
        });
        this.getCompanies();
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        let current_page = this.data.current_page + 1;
        this.setData({
            current_page: current_page
        });
        if(this.data.current_page > this.data.last_page){
            return;
        }
        this.getCompanies();
    },
    getCompanies: function(){
        let data = {};
        if(this.data.swithc_val === true){
            // 默认搜索
            data = {
                keyword: this.data.keyword,
                sort: this.data.sort,
                latitude: this.data.latitude,
                longitude: this.data.longitude,
                attestation: this.data.attestation,
                industry_id: this.data.industry_id,
                select_province: this.data.select_province,
                select_city: this.data.select_city,
            };

            util.get(this.data.next_page_url, data).then((res) => {
                wx.hideLoading();
                let response = res.data.data;
                let companies = [];
                if(this.data.current_page > 1){
                    companies = this.data.companies.concat(response);
                }else{
                    companies = res.data.data;
                }
                this.setData({
                    companies: companies,
                    next_page_url: res.data.next_page_url,
                    last_page: res.data.last_page
                });
                wx.stopPullDownRefresh();
            });
        }else{
            data = {
                params: this.data.params,
                searchType: 'custom',
                keyword: this.data.keyword,
            };

            util.post(this.data.next_page_url, data).then((res) => {
                wx.hideLoading();
                let response = res.data.data;
                let companies = [];
                if(this.data.current_page > 1){
                    companies = this.data.companies.concat(response);
                }else{
                    companies = res.data.data;
                }
                this.setData({
                    companies: companies,
                    next_page_url: res.data.next_page_url,
                    last_page: res.data.last_page
                });
                wx.stopPullDownRefresh();
            });
        }


    },
    // 跳转页面
    navigatorToUrl: function (event) {
        let url = event.currentTarget.dataset.url;
        if(url){
            wx.navigateTo({
                url: url
            });
        }
    },
  changeSwitch(){
      this.setData({
        swithc_val:!this.data.swithc_val
        });

      this.setData({
          next_page_url: api.getCardSquareListUrl,
          current_page: 1,
          last_page: 1,
          companies: [],
      });
      this.getCompanies();
  },
    changeSearch: function (e) {
        let _value = e.detail.value;
        console.log(_value);
        this.setData({
            keyword: _value
        });
    },

    searchBtn: function (e) {
        this.setData({
            companies:[],
            next_page_url: api.getCardSquareListUrl,
            current_page: 1
        });
        //this.cleanCondition(['latitude', 'longitude', 'attestation', 'industry_id', 'select_province', 'select_city', 'sort']);
        this.getCompanies();
    },

    // 设置排序
    setSort: function (event) {
        let sort = event.currentTarget.dataset.sort;
        this.cleanCondition(['latitude', 'longitude', 'attestation', 'industry_id', 'select_province', 'select_city']);
        this.setData({
            sort: sort,
            next_page_url: api.getCardSquareListUrl,
            current_page: 1,
            select_tab_index: 4
        });
        this.getCompanies();
    },

    // 获取经纬度(附近)
    getLocation: function(){
        util.getLocation().then(res => {
            this.cleanCondition(['attestation', 'industry_id', 'select_province', 'select_city']);
            this.setData({
                latitude: res.latitude,
                longitude: res.longitude,
                sort: 'nearby',
                next_page_url: api.getCardSquareListUrl,
                current_page: 1,
                select_tab_index: 2
            });
            this.getCompanies();
        });
    },
    // 认证筛选
    findAttestation: function(){
        this.cleanCondition(['sort', 'latitude', 'longitude', 'industry_id', 'select_province', 'select_city']);
        this.setData({
            attestation: true,
            next_page_url: api.getCardSquareListUrl,
            current_page: 1,
            select_tab_index: 3
        });
        this.getCompanies();
    },

    // 清除条件
    cleanCondition: function(condition){
        if(condition !== undefined){
            let type = typeof condition;
            if(type === 'string'){
                this.setData({
                    [condition]: ''
                })
            }else if(type === 'object'){
                for(let i in condition){
                    this.setData({
                        [condition[i]]: ''
                    })
                }
            }else{
                this.setData({
                    keyword: '',
                    sort: '',
                })
            }
        }else{
            this.setData({
                keyword: '',
                sort: '',
                latitude: '',
                longitude: '',
                attestation: '',
                select_tab_index: -1,
                select_province: '',
                select_city: ''
            })
        }
    },

    getIndustries: function () {
        util.get(api.GetIndustriesUrl).then(res => {
            let response = res.data.data;
            response.unshift({id: 0, name: '不限', parent_id:0, children:[]});
            for(let [index, elem] of response.entries()){
                elem.children.unshift({id: elem.id + '-0', name:'不限', parent_id: elem.id})
            }

            let first_column = []; // 第一列数据
            let second_column = []; // 第二列数据
            let industries = this.data.industries;
            // 设置默认显示的数据
            if(response.length > 0){
                first_column = response;
                if(response[0].children.length > 0){
                    second_column = response[0].children;
                }
                industries[0] = first_column;
                industries[1] = second_column;
            }
            this.setData({
                industryArray: response,
                industries: industries,
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
        if(first_column.children.length > 0){
            let second_column = first_column.children[industry_index[1]]; // 第二列
            if(second_column){
                industry_text = second_column.name;
                industry_id = second_column.id;
            }
        }

        this.cleanCondition(['sort', 'latitude', 'longitude', 'attestation', 'select_province', 'select_city']);
        this.setData({
            industry_text: industry_text,
            industry_id: industry_id,
            next_page_url: api.getCardSquareListUrl,
            current_page: 1,
            select_tab_index: 1
        });
        this.getCompanies();
    },

    changeIndustryIndex: function(event){
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

    getProvinces: function(){
        util.get(api.GetProvincesUrl).then((res) => {
            this.setData({
                provinces: res.data.data
            });
        })
    },
    setProvince: function(event){
        let index = event.detail.value;
        let select_province = this.data.provinces[index];
        this.cleanCondition(['sort', 'latitude', 'longitude', 'attestation', 'industry_id']);
        this.setData({
            province_index: index,
            select_province: select_province,
            next_page_url: api.getCardSquareListUrl,
            current_page: 1,
            select_tab_index: 0
        });
        this.getCompanies();
    },

    getAreas: function(){
        util.get(api.GetAreasUrl).then(res => {
            let response = res.data.data;
            // 存储地区数据
            wx.setStorage({
                key:"AREAS_ARRAY",
                data:JSON.stringify(response)
            });
            this.setData({
                areasArray: response,
                is_show: true,
            });
        })
    },

    changeArea: function (column) {

        let first_column = column.detail.first_column; // 第一列
        let second_column = column.detail.second_column; // 第一列

        let province = first_column.name;
        let city = '';
        if(second_column){
            city = second_column.name;
        }


        this.cleanCondition(['sort', 'latitude', 'longitude', 'attestation', 'industry_id']);
        this.setData({
            select_province: province,
            select_city: city,
            next_page_url: api.getCardSquareListUrl,
            current_page: 1,
            select_tab_index: 0
        });
        this.getCompanies();
    },
    back: function(){
        wx.navigateBack();
    },
    checkLogin: function () {
        util.get(api.CheckLoginUrl, {}, false).then(response => {
            let is_login = response.data.data.is_login;
            if(is_login === true){
                util.get(api.GetCustomSearch, {}, false).then(response => {
                    let params = response.data.data.userScreen;
                    this.setData({
                        params: params,
                    })
                })
            }
            this.setData({
                is_login
            })
        })
    }

})