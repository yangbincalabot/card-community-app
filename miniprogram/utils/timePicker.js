function withData(param) {
  return param < 10 ? '0' + param : '' + param;
}
function getLoopArray(start, end) {
  var start = start || 0;
  var end = end || 1;
  var array = [];
  for (var i = start; i <= end; i++) {
    array.push(withData(i));
  }
  return array;
}
function getNewDateArry() {
  // 当前时间的处理
  var newDate = new Date();
  var hour = withData(newDate.getHours()),
    minu = withData(newDate.getMinutes());
  return [hour, minu];
}
function dateTimePicker( date) {
  // 返回默认显示的数组和联动数组的声明
  var dateTime = [], dateTimeArray = [[], []];
  // 默认开始显示数据
  console.log(date)
  var defaultDate = date ? [...date.split(':')] : getNewDateArry();
  // 处理联动列表数据
  /*时分*/
  dateTimeArray[0] = getLoopArray(0, 23);
  dateTimeArray[1] = ["00", "15", "30", "45"];

  dateTimeArray.forEach((current, index) => {
    let _item = current.indexOf(defaultDate[index]);
    if (_item < 0) {
      _item = 0;
    }
    dateTime.push(_item);
  });

  return {
    dateTimeArray: dateTimeArray,
    dateTime: dateTime
  }
}
module.exports = {
  dateTimePicker: dateTimePicker
}