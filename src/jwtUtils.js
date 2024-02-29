///////////////////////////////////////////////////////////////////////////////
// jwtUtils.js
// ===========
// JWT utility on client-side
//
//  AUTHOR: Song Ho Ahn (song.ahn@gmail.com)
// CREATED: 2024-01-23
// UPDATED: 2024-01-30
///////////////////////////////////////////////////////////////////////////////



///////////////////////////////////////////////////////////////////////////////
// decode JWT to JS object
function decodeJwt(jwt="")
{
    // return object
    obj = { header:{}, payload:{}, signature:{} };

    let tokens = jwt.split('.');
    // parse JWT header
    if(tokens[0])
    {
        obj.header = JSON.parse(decodeBase64Url(tokens[0]));
    }
    if(tokens[1])
    {
        obj.payload = JSON.parse(decodeBase64Url(tokens[1]));
    }
    if(tokens[2])
    {
        //console.log(tokens[2]);
        obj.signature = tokens[2];
    }
    return obj;
}



///////////////////////////////////////////////////////////////////////////////
// convert Base64URL encoding to a string
// NOTE: must replace Base64URL to Base64 ("-"=>"+", "_"=>"/") before decode
function decodeBase64Url(data="")
{
    return atob(data.replace(/-/g, "+").replace(/_/g, "/"));
}

///////////////////////////////////////////////////////////////////////////////
// convert Base64URL encoding to a string
// NOTE: must replace Base64 to Base64URL ("+"=>"-", "/"=>"_")
function encodeBase64Url(data="")
{
    return btoa(data).replace(/\+/g, "-").replace(/\//g, "_");
}
