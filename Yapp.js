var API = (function(){

        const apikey = 'testur'; //change this once we use a real api key
        const API_PATH = 'https://vitogo.hipposerver.com/api.php'; //the url of the api endpoint
        var token = '';
        var ttl = 0;
        var opened;
        
        function open(callback){
                $.post(
                        API_PATH, {
                                action: 'open',
                                key: apikey
                        }, 
                        function(data, textStatus, XMLHttpRequest){
                                if(data && data.done == '1'){
                                        var lol = new Date();
                                        opened = lol.getTime();
                                        token = data.data.api_token;
                                        ttl = data.data.ttl;
                                        callback? callback.name(callback.params) : $.noop();
                                }else{
                                        console.log(data.message);
                                        return false;
                                }
                        },
                        'json'
                );
        };
        
        function close(callback){
                $.post(
                        API_PATH, {
                                action: 'close',
                                api: token
                        }, 
                        function(data, textStatus, XMLHttpRequest){
                                if(data && data.done == '1'){
                                        callback? callback.name(callback.params) : $.noop();
                                }else{
                                        console.log(data.message);
                                }
                        },
                        'json'
                );
        };
        
        function isOpen(){
                if(token == ''){
                        return false;
                }else{
                        var lol = new Date();
                        return ((lol.getTime() - opened) / 1000)<= ttl;
                }
        }

        return { // public interface
                call: function(params) {
                        if(!isOpen()){
                                open({name: API.call, params: params});
                        }else{
                                postdata = $.extend({'api': token, 'action': params.action}, params.args);
                                $.post(
                                        API_PATH, 
                                        postdata,
                                        function(data, textStatus, XMLHttpRequest){
                                                if(data && data.done == '1'){
                                                        var lol = new Date();
                                                        opened = lol.getTime();
                                                        var data = data.data;
                                                        (params.onComplete)? params.onComplete(data) : $.noop();
                                                }else{
                                                        console.log(data.message);
                                                        (params.onFailure)? params.onFailure(data) : $.noop();
                                                        return false;
                                                }
                                        },
                                        'json'
                                );
                        }
                }
        };
})();
