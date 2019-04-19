<?php

return [
	"ttl"=>"999999",//token有效期（分钟）
//    "refresh_ttl"=>"",//刷新token时间（分钟）
//    "algo"=>"RS256",//token签名算法
//    "user"=> App\ScUser::class,//指向User模型的命名空间路径
//    "identifier"=>"",//用于从token的sub中获取用户
//    "require_claims"=>"",//必须出现在token的payload中的选项，否则会抛出TokenInvalidException异常
	"blacklist_enabled"=>true,//如果该选项被设置为false，那么我们将不能废止token，即使我们刷新了token，前一个token仍然有效
//    "providers"=>[//完成各种任务的具体实现，如果需要的话你可以重写他们
//        "user"=>App\ScUser::class,// providers.user：基于sub获取用户的实现
//        "JWT"=>"2sNEeCsdIaO35HlbmVrOFUx7cCZgIYPD",// providers.jwt：加密/解密token
//        "Authentication"=>"",// providers.auth：通过证书/ID获取认证用户
//        "Storage"=>"",// providers.storage：存储token直到它们失效
//    ],
	"user"=>App\Models\User::class,// providers.user：基于sub获取用户的实现
];
