/**
 * Created by saive on 17-2-21.
 * 活动的公共js代码
 */

var active = {
    init : function(itemsObj,active) {
        var items = new Vue({
            el : '#page-wrapper',
            data : {
                items : itemsObj,
                active : active,
                other : {
                    rechargeLevel:2,
                    rechargeLevels:[],
                    rechargeDays : 2,
                }

            },
            methods: {

                reDrawRechargeLevel:function(){ //重绘储值档次
                    var _self = this;
                    console.log('----');
                    // console.log(_self.other.rechargeLevel);
                },

                editItem:function(active_id) {  //修改活动
                    var _self = this;
                    $.post('/active-find',{aid:active_id},function(callback){
                        if(callback.code == 2000) {
                            $('#active_info').show();
                            _self.active = callback.data.common;
                        }
                        // $('#active_info').show();
                        console.log(callback);
                    },'json');
                },
                saveActive:function() {
                    var _self = this;
                    var begin_time = $('#begin_time').val();
                    var end_time = $('#end_time').val();
                    _self.active.begin_time = begin_time;
                    _self.active.end_time = end_time;
                    $.post('/active-ajaxnotice.html',{active:_self.active},function(callback){
                        if(callback.code == 2000) {
                            window.location.reload();
                        } else {
                            alert(callback.msg);
                        }
                    },'json');
                },
                changeItem : function(active_id,status) {   //修改状态
                    if(status == 1) {
                        var text = '确定要禁用该活动吗？';
                        var _status = 0;
                    } else {
                        var text = '确定要开启该活动吗？';
                        var _status = 1;
                    }
                    if(confirm(text)) {
                        $.post('/active-change',{aid:active_id,status:_status},function(callback){
                            if(callback.code == 2000) {
                                window.location.reload();
                            } else {
                                alert(callback.msg);
                            }
                        },'json');
                    }
                },
                copyItem:function(active_id) {  //复制活动
                    $.post('/active-copy',{aid:active_id},function(callback){

                        if(callback.code == 2000) {
                            window.location.reload();
                        } else {
                            alert(callback.msg);
                        }

                    },'json');
                },
                deleteItem:function(active_id,status) {   //删除活动
                    if(status == 1) {
                        alert('抱歉该活动尚未禁用~');
                    } else {
                        if(confirm('确定要删除活动吗?')) {
                            $.post('/active-delete',{aid:active_id},function(callback){
                                if(callback.code == 2000) {
                                    window.location.reload();
                                } else {
                                    alert(callback.msg);
                                }
                            },'json');
                        }
                    }
                },
                saveItems: function() {
                    var _self = this;
                    $.post('/rbac-savegroups',{items:_self.items},function(callback){
                        if(callback.code == 200) {
                            window.location.reload();
                        } else {
                            alert(callback.msg);
                        }
                    },'json');
                }
            }
        });
    }
};




$(function(){
    $('#show_hide').click(function(){

        $('#active_info').toggle('normal');

    });
})

