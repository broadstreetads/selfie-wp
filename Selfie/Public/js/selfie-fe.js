/* Globals, schmobels */
window.selfieLikeCache = {};

function selfieLiked(event, post_id, position) {
    // Make sure the user can't like it twice
    var key = post_id + '-' + position;
    
    if(window.selfieLikeCache[key]) 
        return;
    else
        window.selfieLikeCache[key] = true;
    
    event.stopPropagation();
    
    // Construct URLs/Ids
    var url = selfieAjax + '?action=sf_like_selfie&post=' + post_id + '&position=' + position;
    var id = 'selfie-count-' + post_id + '-' + position;
        
    // Call the backend    
    selfieJax(url, function() {});
    
    // Update the view
    var el = document.getElementById(id);
    var likes = parseInt(el.innerHTML);    
    el.innerHTML = ++likes;
}

/* Cross-browser mini ajax function to avoid requiring/conflicting with jQuery */
function selfieJax(url, cb, method, post, contenttype) {
    var requestTimeout,xhr;
    
    try { 
        xhr = new XMLHttpRequest(); 
    } catch(e) {        
        try { 
            xhr = new ActiveXObject("Msxml2.XMLHTTP"); 
        } catch (e) { 
            if (console) console.log("selfieJax: XMLHttpRequest not supported");  
            return null;
        }
    }
    
    requestTimeout = setTimeout(function() {
        xhr.abort(); 
        cb(new Error("selfieJax: aborted by a timeout"), "",xhr); 
    }, 10000);
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState != 4) return;
        clearTimeout(requestTimeout);
        cb(xhr.status != 200 
            ? new Error("selfieJax: server respnse status is " + xhr.status) 
            : false,
            xhr.responseText, xhr);
    }
    
    xhr.open(method ? method.toUpperCase() : "GET", url, true);
  
    if(!post) {
        xhr.send();        
    } else {
        xhr.setRequestHeader('Content-type', contenttype ? contenttype : 'application/x-www-form-urlencoded');
        xhr.send(post)
    }
}