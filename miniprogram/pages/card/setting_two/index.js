const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        cityShow: false,
        provinces: [],
        citys_selected: [],
        industry: [],
        industry_selected: [],


        parentText: '', // 一级主题
        childText: '',  // 子级主题
        selectText: '区域选择',
        childData: [],
        

        userScreen: {},
    },

    pageData: {
        areasArray: [],
        industryArray: [],
        choiseType: 'area',
        areaParams: [],
        industryParams: [],

    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        this.getAreas();
        this.getIndustries();
    },
    closePup() {
        this.setData({
            cityShow: false,
            childText: '',
        })
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
        this.getCustomSearch();
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
    getAreas: function () {
        wx.getStorage({
            key: 'AREAS_ARRAY',
            success: res => {
                let area_array = JSON.parse(res.data);
                this.pageData.areasArray = area_array;
                this.setProvinceData();
            },
            fail: () => {
                util.get(api.GetAreasUrl, {}, false).then(res => {
                    let response = res.data.data;
                    wx.setStorage({
                        key: "AREAS_ARRAY",
                        data: JSON.stringify(response)
                    });
                    this.pageData.areasArray = response;
                    this.setProvinceData();
                })
            }
        });
    },

    getIndustries: function(){
        wx.getStorage({
            key: 'INDUSTRY_ARRAY',
            success: res => {
                let industry_array = JSON.parse(res.data);
                this.pageData.industryArray = industry_array;
                this.setInstrutyData();
            },
            fail: () => {
                util.get(api.GetIndustriesUrl, {}, false).then(res => {
                    let response = res.data.data;
                    wx.setStorage({
                        key: "INDUSTRY_ARRAY",
                        data: JSON.stringify(response)
                    });
                    this.pageData.industryArray = response;
                    this.setInstrutyData();
                })
            }
        });
    },

    // 初始化所有省份的显示
    setProvinceData: function(areaData){
        areaData = areaData ? areaData : this.pageData.areasArray;
        let provinces = [];
        for(let item of areaData.values()){
            provinces.push({
                label: item.name
            })
        }
        this.setData({
            provinces
        })
    },
    // 初始化一级行业显示
    setInstrutyData: function(instrutyData){
        instrutyData = instrutyData ? instrutyData : this.pageData.industryArray;
        let industry = [];
        for(let item of instrutyData.values()){
            industry.push({
                label: item.name
            })
        }
        this.setData({industry})
    },

    // 根据点击的省份选择城市
    choiceProvince: function(event){
        let province = event.currentTarget.dataset.province;
        let currentItem = this.pageData.areasArray.find((value) => value.name === province);
        let children = currentItem.children;
        this.pageData.choiseType = 'area';
        if(children && children.length > 0){
            let citys = [];
            for(let item of children){
                citys.push(item.name);
            }
            this.setData({ childData: citys, parentText: province, cityShow: true, selectText: '区域选择'  });
        }else{
            // 直接添加到数据
            this.data.citys_selected.push({ label: province })
            this.setData({
                citys_selected: this.data.citys_selected
            })
            this.pageData.areaParams.push({
                province: province,
                city: ''
            })
        }
    },
    

    // 根据点击的行业选择二级行业
    choiseParentIndustry: function(event){
        this.pageData.choiseType = 'industry';
        let name = event.currentTarget.dataset.name;
        let currentData = this.pageData.industryArray.find((value) => value.name === name);
        let children = currentData.children;
        if (children && children.length > 0) {
            let industry = [];
            for (let item of children) {
                industry.push(item.name);
            }
            this.setData({ childData: industry, parentText: name, cityShow: true, selectText: '行业选择' });
        } else {
            // 直接添加到数据
            this.data.industry_selected.push({label: name});
            this.setData({
                industry_selected: this.data.industry_selected
            })
            let industryItem = this.pageData.industryArray.find((item) => item.name === name); 

            this.pageData.industryParams.push({
                industry_id: industryItem.id,
                name
            })
        }
    },


    // 选中子集
    choiceChild: function (event) {
        let name = event.currentTarget.dataset.name;
        this.setData({ childText: name })
    },

    // 点击确定按钮
    confirm: function(event){
        let type = this.pageData.choiseType;
        let name = this.data.childText ? this.data.childText : this.data.parentText;
        if (type === 'area'){
            this.data.citys_selected.push({ label: name })
            
            this.pageData.areaParams.push({
                province: this.data.parentText,
                city: this.data.childText
            });


            this.setData({
                citys_selected: this.data.citys_selected,
                cityShow: false,
                childText: '',
            });
        }else{
            this.data.industry_selected.push({ label: name })
            this.setData({
                industry_selected: this.data.industry_selected,
                cityShow: false,
                childText: '',
            });

            
            let parentItem = this.pageData.industryArray.find((item) => item.name === this.data.parentText);
            let industryItem = parentItem.children.find((item) => item.name === name);
            this.pageData.industryParams.push({
                industry_id: industryItem && industryItem.id ? industryItem.id : parentItem.id,
                name
            });
        }
    },

    onDelete: function(event){
        let type = event.currentTarget.dataset.type;
        let index = event.currentTarget.dataset.index;
        let deleteData, deleteIndex;
        if(type === 'area'){
            deleteData = this.data.citys_selected[index];
            deleteIndex = this.pageData.areaParams.findIndex((item) => item.province === deleteData.label || item.city === deleteData.label);
            this.pageData.areaParams.splice(deleteIndex, 1);



            this.data.citys_selected.splice(index, 1);
            this.setData({
                citys_selected: this.data.citys_selected
            });

        }else{
            deleteData = this.data.industry_selected[index];
            deleteIndex = this.pageData.industryParams.findIndex((item) => item.name === deleteData.label)
            this.pageData.industryParams.splice(deleteIndex, 1);


            this.data.industry_selected.splice(index, 1);
            this.setData({
                industry_selected: this.data.industry_selected
            });
            ;
            
        }
    },


    // 搜索
    search: function(){
        console.log(this.pageData.areaParams);
        console.log(this.pageData.industryParams);


        const eventChannel = this.getOpenerEventChannel();

        let params = {
            area: this.pageData.areaParams,
            industry: this.pageData.industryParams
        };

        //保存搜索配置到数据库
        util.post(api.StoreCustomSearch, { params }, false);
        setTimeout(() => {
            eventChannel.emit('custom-search', params);
            wx.navigateBack();
        }, 500);
    },


    getCustomSearch: function () {
        util.get(api.GetCustomSearch, {}, false).then(response => {
            let userScreen = response.data.data.userScreen;
            this.pageData.areaParams = userScreen.area ? userScreen.area : [];
            this.pageData.industryParams = userScreen.industry ? userScreen.industry : [];

            let citys_selected = [];
            let industry_selected = [];
            for(let item of this.pageData.areaParams.values()){
                 if(item.city){
                     citys_selected.push({ label: item.city });
                 }else{
                     citys_selected.push({ label: item.province });
                 }
            }

            for(let item of this.pageData.industryParams.values()){
                industry_selected.push({label: item.name })
            }


            // 初始化选中数据
            this.setData({
                userScreen: userScreen,
                is_show: true,
                industry_selected,
                citys_selected,
            })
        })
    },

})