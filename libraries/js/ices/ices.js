var ICES_PATH = {base_url:''};

Date.prototype.format = function(format) {
    var returnStr = '';
    var replace = Date.replaceChars;
    for (var i = 0; i < format.length; i++) {       var curChar = format.charAt(i);         if (i - 1 >= 0 && format.charAt(i - 1) == "\\") {
            returnStr += curChar;
        }
        else if (replace[curChar]) {
            returnStr += replace[curChar].call(this);
        } else if (curChar != "\\"){
            returnStr += curChar;
        }
    }
    return returnStr;
};

Date.replaceChars = {
    shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    longMonths: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    shortDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    longDays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],

    // Day
    d: function() { return (this.getDate() < 10 ? '0' : '') + this.getDate(); },
    D: function() { return Date.replaceChars.shortDays[this.getDay()]; },
    j: function() { return this.getDate(); },
    l: function() { return Date.replaceChars.longDays[this.getDay()]; },
    N: function() { return this.getDay() + 1; },
    S: function() { return (this.getDate() % 10 == 1 && this.getDate() != 11 ? 'st' : (this.getDate() % 10 == 2 && this.getDate() != 12 ? 'nd' : (this.getDate() % 10 == 3 && this.getDate() != 13 ? 'rd' : 'th'))); },
    w: function() { return this.getDay(); },
    z: function() { var d = new Date(this.getFullYear(),0,1); return Math.ceil((this - d) / 86400000); }, // Fixed now
    // Week
    W: function() { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((((this - d) / 86400000) + d.getDay() + 1) / 7); }, // Fixed now
    // Month
    F: function() { return Date.replaceChars.longMonths[this.getMonth()]; },
    m: function() { return (this.getMonth() < 9 ? '0' : '') + (this.getMonth() + 1); },
    M: function() { return Date.replaceChars.shortMonths[this.getMonth()]; },
    n: function() { return this.getMonth() + 1; },
    t: function() { var d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 0).getDate() }, // Fixed now, gets #days of date
    // Year
    L: function() { var year = this.getFullYear(); return (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)); },   // Fixed now
    o: function() { var d  = new Date(this.valueOf());  d.setDate(d.getDate() - ((this.getDay() + 6) % 7) + 3); return d.getFullYear();}, //Fixed now
    Y: function() { return this.getFullYear(); },
    y: function() { return ('' + this.getFullYear()).substr(2); },
    // Time
    a: function() { return this.getHours() < 12 ? 'am' : 'pm'; },
    A: function() { return this.getHours() < 12 ? 'AM' : 'PM'; },
    B: function() { return Math.floor((((this.getUTCHours() + 1) % 24) + this.getUTCMinutes() / 60 + this.getUTCSeconds() / 3600) * 1000 / 24); }, // Fixed now
    g: function() { return this.getHours() % 12 || 12; },
    G: function() { return this.getHours(); },
    h: function() { return ((this.getHours() % 12 || 12) < 10 ? '0' : '') + (this.getHours() % 12 || 12); },
    H: function() { return (this.getHours() < 10 ? '0' : '') + this.getHours(); },
    i: function() { return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes(); },
    s: function() { return (this.getSeconds() < 10 ? '0' : '') + this.getSeconds(); },
    u: function() { var m = this.getMilliseconds(); return (m < 10 ? '00' : (m < 100 ?
'0' : '')) + m; },
    // Timezone
    e: function() { return "Not Yet Supported"; },
    I: function() {
        var DST = null;
            for (var i = 0; i < 12; ++i) {
                    var d = new Date(this.getFullYear(), i, 1);
                    var offset = d.getTimezoneOffset();

                    if (DST === null) DST = offset;
                    else if (offset < DST) { DST = offset; break; }                     else if (offset > DST) break;
            }
            return (this.getTimezoneOffset() == DST) | 0;
        },
    O: function() { return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + '00'; },
    P: function() { return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + ':00'; }, // Fixed now
    T: function() { var m = this.getMonth(); this.setMonth(0); var result = this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/, '$1'); this.setMonth(m); return result;},
    Z: function() { return -this.getTimezoneOffset() * 60; },
    // Full Date/Time
    c: function() { return this.format("Y-m-d\\TH:i:sP"); }, // Fixed now
    r: function() { return this.toString(); },
    U: function() { return this.getTime() / 1000; }
};

$.fn.insertAt = function(elements, index){
    var children = this.children();
    if(index >= children.size()){
        this.append(elements);
        return this;
    }
    var before = children.eq(index);
    $(elements).insertBefore(before);
    return this;
};

var ICES_DATA_TRANSFER = {
    ajaxPOST:function($url,$data){
        if($data !== null && typeof $data !== 'undefined'){
            if(typeof $data.ajax_post === 'undefined') $data.ajax_post = true;
        }
        
        var ldata_str = JSON.stringify($data,null,null);
        ldata_str = btoa(ldata_str);
        var response = $.ajax({
            type: "POST",
            url: $url,
            data:ldata_str,
            dataType: 'json',
            contentType: 'application/json;charset=utf-8',
            global: false,
            async:false,
            cache: false,
            success: function(data){
            }
        }).responseText;

        try{response = JSON.parse(response);} 
        catch(err){            
            try{
                response = response.toString();
            }
            catch(err2){
                response = '';
            }
            
        }
        return response;
    },
    common_ajax_listener:function(iparam,idata){
        if(typeof(idata)==='undefined') idata = {};
        var lresult = ICES_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+'common_ajax_listener/'+iparam,idata);
        return lresult;
    },
    
}

var ICES_GENERATOR={
    UNIQUEID:function(){
        return 'xxxxxxxxxxxxxxxxxxxxxxx'.replace(/[x]/g, function(c) {
            var r = Math.random()*16|0, v = c === 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        });
    },
    CURR_DATE:function(){
        var now     = new Date(); 
        var date = now.format('Y-m-d');
        return date;
        
    },
    CURR_TIME:function(){
        var now     = new Date(); 
        var time = now.format('H:i:s');
        return time;
        
    },
    CURR_DATETIME:function(opt,val, dateformat){
        var now     = new Date(); 
        var year    = now.getFullYear();
        var month   = now.getMonth(); 
        var day     = now.getDate();
        var hour    = now.getHours();
        var minute  = now.getMinutes();
        var second  = now.getSeconds(); 
        
        if(typeof opt === 'undefined') opt = null;
        if(typeof val === 'undefined') val = null;
        if(typeof dateformat === 'undefined') dateformat = null;
        
        if(opt !== null){
            switch(opt){
                case 'year': year = year+val; break;
                case 'month': month = month+val; break;
                case 'day': day = day+val; break;
                case 'hour': hour = hour+val; break;
                case 'minute': minute = minute+val; break;
                case 'second': second = second+val; break;
                    
            }
        }
        
        var ldatetime = new Date(year,month,day,hour,minute,second);
        if(dateformat === null) dateformat = 'Y-m-d H:i:s'
        ldatetime = ldatetime.format(dateformat);
        return ldatetime;
    }
}

var ICES_CONVERTER={
    thousand_separator: function(rp, dec) {
            if(typeof dec ==='undefined') dec = 2;
            if(parseFloat(rp) == 0) return '0.00';
            if(typeof rp === 'undefined') return '0.00';
            if(rp == null ) return '';
            if(rp == '') return '0.00';            
            rp =""+rp;
            rp = (Math.round(parseFloat(rp)*Math.pow(10,dec))/(Math.pow(10,dec))).toString();
            var rupiah = "";
            var vfloat = "";

            var minus_str = "";
            
            var check = true;
            while(check){
                if(rp.length>0){
                    if(rp.substr(0,1) == '0'){
                        rp = rp.substr(1,rp.length-1);
                    }
                    else
                        check = false;
                }
                else check = false;
            }

            if (rp.indexOf("-")>=0) {
                    minus_str = rp.substring(rp.indexOf("-"),1);
                    rp = rp.substring(rp.indexOf("-")+1);
            }

            if (rp.indexOf(".")>=0) {
                    vfloat = rp.substring(rp.indexOf("."));
                    rp = rp.substring(0,rp.indexOf("."));
            }
            else vfloat = '.00';
            p = rp.length;
            while(p > 3) {
                    rupiah = "," + rp.substring(p-3) + rupiah;
                    l = rp.length - 3;
                    rp = rp.substring(0,l);
                    p = rp.length;
            }
            rupiah = rp + rupiah;
            return minus_str+rupiah+vfloat;
    },
    _str:function(data){
        var result = '';
        if(typeof data !== 'undefined'){
            if (data !== null){
                result = data.toString();
            }
        }
        return result;
    },
    _float:function(data){
        data = String(data);
        data = data.replace(/[^0-9.]/g,'');
        if(isNaN(parseFloat(data))) data = '0';
        return parseFloat(data);
        
    },
    _int:function(data){
        data = String(data);
        data = data.replace(/[^0-9.]/g,'');
        if(isNaN(parseInt(data))) data = '0';
        return parseInt(data);
        
    },
    _escape:function(data) {
        var replace_map = {
            '\\': '&#92;',
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            "/": '&#47;'
        };

        return String(data).replace(/[&<>"'\/\\]/g, function (match) {
            return replace_map[match];
        });
    }
}

var ICES_USER = {
    check_timeout:function(){        
        var lresult = ICES_DATA_TRANSFER.common_ajax_listener('is_timeout/',null).response;
        return lresult;
        
    }
};

