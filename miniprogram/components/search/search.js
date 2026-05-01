// components/search/search.js
Component({
    /**
     * 组件的属性列表
     */
    properties: {
        item: {
            type: Object,
            value: {},
        },
        industries: {
            type : Array,
            value: [],
        },
        industryArray: {
            type: Array,
            value: [],
        },
        areasArray: {
            type : Array,
            value: [],
        },

        areas: {
            type : Array,
            value : [],
        },



    },

    /**
     * 组件的初始数据
     */
    data: {
        industry_index: [0, 0], // 默认行业选择索引,
        industry_text: '',
        industry_id: 0,

        area_index: [0, 0],
        area_text: '',

      //  areas: [[], []], // picker 显示
    },

    /**
     * 组件的方法列表
     */
    methods: {
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


            this.data.item.industry_id = industry_id;
            this.setData({
                industry_text: industry_text,
                industry_id: industry_id,
                item: this.data.item,
            });

            this.triggerEvent('changeSearch', this.data.item)
        },
        changeIndustryColumn: function (event) {
           // console.log('修改的列为', event.detail.column, '，值为', event.detail.value);
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
            // this.setData(data);
            this.setData({
                industries: data.industries
            });
        },

        initArea: function () {
            let first_column = []; // 第一列数据
            let second_column = []; // 第二列数据

            let areas = this.data.areas;

            let items = this.data.areasArray;
            if(items.length > 0){
                first_column = items;
                if(items[0].children.length > 0){
                    second_column = items[0].children;
                }
                areas[0] = first_column;
                areas[1] = second_column;
            }
            this.setData({
                areas: areas
            })
            this.setAreaIndex();
        },

        changeAreaColumn: function(event) {
            let data = {
                areas: this.data.areas,
                area_index:  this.data.area_index,
            };
            data.area_index[event.detail.column] = event.detail.value;
            switch (event.detail.column) {
                case 0:
                    data.areas[1] = this.data.areasArray[event.detail.value].children;
                    data.area_index[1] = 0;
                    break;
                case 1:
                    break;
            }
            this.setData(data);
            // this.setData({
            //     areas: data.areas
            // });
        },

        // 修改值
        changeArea: function(event){
            // 与父级通讯,根据要求可接收第二个参数
            let index = event.detail.value;
            let text = this.data.areasArray[index[0]].name + ',' + this.data.areasArray[index[0]].children[index[1]].name;
            this.data.item.province = this.data.areasArray[index[0]].name;
            this.data.item.city = this.data.areasArray[index[0]].children[index[1]].name;
            this.setData({
                area_index: index,
                area_text: text,
                item: this.data.item
            });


            this.triggerEvent('changeSearch', this.data.item)
        },

        setIndustryIndex: function () {
            let industryArray = this.data.industryArray;
            let industry_index = this.data.industry_index;
            let industries = this.data.industries;
            let industry_text = '';
            if(industryArray && this.data.item.industry_id > 0){
                industry_text = this.data.item.industry.name;
                if(this.data.item.industry){
                    // 有父级
                    for(let index in industryArray){
                        if(industryArray[index].id === this.data.item.industry.parent_id){
                            industry_index[0] = index;
                            // 遍历二级数据
                            for(let i in industryArray[index].children){
                                if(industryArray[index].children[i].id === this.data.item.industry.id){
                                    industry_index[1] = i;
                                    break;
                                }
                            }
                            industries[1] = industryArray[index].children; // 设置二级数据
                            break;
                        }
                    }


                }else{
                    // 无父级
                    for(let index in industryArray){
                        if(industryArray[index].id === this.data.item.industry.id){
                            industry_index[0] = index;
                            industries[1] = []; // 清空二级数据
                            break;
                        }
                    }
                }
            }
            console.log(industry_index);
            this.setData({
                industry_index: industry_index,
                industries: industries,
                industry_text: industry_text
            })
        },
        setAreaIndex: function () {
            let area_index = this.data.area_index;
            let areasArray = this.data.areasArray;
            let areas = this.data.areas;
            let text = '';
            if((this.data.item.province || this.data.item.city) && areasArray){
                if(this.data.item.province && this.data.item.city){
                    text = this.data.item.province + ',' + this.data.item.city;
                    for(let index in areasArray){
                        if(areasArray[index].name === this.data.item.province){
                            area_index[0] = index;
                            // 遍历二级数据
                            for(let i in areasArray[index].children){
                                if(areasArray[index].children[i].name === this.data.item.city){
                                    area_index[1] = i;
                                    break;
                                }
                            }
                            areas[1] = areasArray[index].children; // 设置二级数据
                            break;
                        }
                    }
                }else{
                    text = this.data.item.province;
                    for(let index in areasArray){
                        if(areasArray[index].name === this.data.item.province){
                            area_index[0] = index;
                            areas[1] = []; // 清空二级数据
                            break;
                        }
                    }
                }
                this.setData({
                    area_index: area_index,
                    areas: areas,
                    area_text: text
                })
            }
        },

        switchChange(e) {
            this.data.item.attestation = !this.data.item.attestation;
            this.setData({
                item: this.data.item
            });
            this.triggerEvent('changeSearch', this.data.item)
        },
        switchActive(e){
            this.data.item.is_active = !this.data.item.is_active;
            this.setData({
                item: this.data.item
            });
            this.triggerEvent('changeSearch', this.data.item)
        }
    },
    lifetimes: {
        attached: function(){
            this.setData({
                area_index: [0,0]
            });
            
            this.initArea();
            this.setIndustryIndex();
            //this.setAreaIndex();
        },

        ready: function(){

        }
    },
    observers: {
        'industry_index'(industry_index){
         //   console.log(this.data.item.id + '----' + industry_index)
        },
        'area_index'(area_index){
        //   console.log(this.data.item.id + '----' + area_index)
        }
    }
})
