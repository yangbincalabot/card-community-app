const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({
  data: {
    formats: {},
    bottom: 0,
    readOnly: false,
    placeholder: '开始输入...',
    _focus: false,
    content:''
  },

  onLoad: function (options) {
    let that = this;
    let _content = wx.getStorageSync('activity_content');
    if (_content) {
      that.setData({
        content: _content
      })
    }
    
  },

  editorWx: function (e) {
    let _content = e.detail.html;
    console.log(_content)
    this.setData({
      'content': _content
    })
  },

  realSubmit: function () {
    let that = this;
    let _content = that.data.content;
    if (_content) {
      wx.setStorageSync('activity_content', _content);
    }
    setTimeout(function() {
      wx.navigateBack({
        delta:1
      })
    },200);
  },

  readOnlyChange() {
    this.setData({
      readOnly: !this.data.readOnly
    })
  },

  onEditorReady() {
    const that = this
    wx.createSelectorQuery().select('#editor').context(function (res) {
      that.editorCtx = res.context
      let EditorContext = res.context;
      setTimeout(function () {
        let _content = that.data.content;
        let _new_content = '';
        if (_content) {
          _new_content = _content + '<br /><br />';
        }
        let obj = { html: _new_content };
        EditorContext.setContents(obj);
      }, 800);
    }).exec()
  },

  undo() {
    this.editorCtx.undo()
  },
  redo() {
    this.editorCtx.redo()
  },
  format(e) {
    let { name, value } = e.currentTarget.dataset
    if (!name) return
    // console.log('format', name, value)
    this.editorCtx.format(name, value)

  },
  onStatusChange(e) {
    const formats = e.detail
    this.setData({ formats })
  },
  insertDivider() {
    this.editorCtx.insertDivider({
      success: function () {
        console.log('insert divider success')
      }
    })
  },
  clear() {
    this.editorCtx.clear({
      success: function (res) {
        console.log("clear success")
      }
    })
  },
  removeFormat() {
    this.editorCtx.removeFormat()
  },
  insertDate() {
    const date = new Date()
    const formatDate = `${date.getFullYear()}/${date.getMonth() + 1}/${date.getDate()}`
    this.editorCtx.insertText({
      text: formatDate
    })
  },
  insertImage() {
    const that = this
    util.fliesUpload().then((respond) => {
      let uploadResponse = JSON.parse(respond.data);
      that.editorCtx.insertImage({
        src: uploadResponse.url,
        data: {
          id: 'abcd',
          role: 'god'
        },
        success: function () {
          console.log('insert image success');
          that.editorCtx.insertText({
            text: "\n"
          })
        }
      })
    })
  }
})
