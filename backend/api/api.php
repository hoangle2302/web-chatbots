<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client();

$response = $client->post('https://chat.qwen.ai/api/v2/chat/completions', [
    'query' => [
        'chat_id' => '78af31b1-70cd-4674-98a9-aaa656e590c6'
    ],
    'headers' => [
        'Accept'             => '*/*',
        'Accept-Language'    => 'vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5',
        'Connection'         => 'keep-alive',
        'Origin'             => 'https://chat.qwen.ai',
        'Referer'            => 'https://chat.qwen.ai/c/guest',
        'Sec-Fetch-Dest'     => 'empty',
        'Sec-Fetch-Mode'     => 'cors',
        'Sec-Fetch-Site'     => 'same-origin',
        'User-Agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
        'authorization'      => 'Bearer',
        'bx-ua'              => '231!QdA36kmUx54+jmPBE43q0MEjUq/YvqY2leOxacSC80vTPuB9lMZY9mRWFzrwLEV0PmcfY4rL2l/3tvLpMixvmLs4/dJSz0t1vc7YklXEtL8pkK9EFFfw0gJWYCcYa3hK3l/cGDuw+zd+xTgDK8e5yPbCD0oyUU0a0Y22idh7TddWeyKxXadoHvAk1duC3nxtbTYuv9dzp+EVc5Eysz3vZpnCdlgNSi+nrtoWRa0xaeD+0u8e+Zd++6WF1cCLPVjYHJBh+++j+ygU3+jOQN/IRk4cFkk3kmOEh5KeTlmTPdUHHpT3UabPjdw5bFPotNc6cKAMqYZImTp6hit8zJG1EoBzNSBNr3Ew5xFfbX+8se9KnfN+uYYviKuII/mi/zPVWvQ0QeLCSF1VxsXcNZ4iERubJKCK9SaYcq/NJSkzp97PlbJ6j424p5Vohre+a6OE0sHDtRJo4qlRKULoVJc9iuEq907S+6C3oDnGHHRfi30g5j5fHSgqhOAhTt38TkqMJpHlUU8QDUPhIx/0xTv+ytIIX0klKT4awz4SbQ1ed9Wz6zVuh2hdvTeMTss1YWMbJy4xKfhUP6bJBfieFIktc+oaBfeOx9rEDWCJ2tVxwrQckai5vG/D14z/WfbvCR52gyyNUcBUVUDgbXBUWeTB92TFKUNqFU2xzVniVWWmcfyq5icNIJMffT6M/vbFgmy9C2QDPGSYT0rR2dG5W0bLarcT0TKMEWl9huVWVhh0o4P36rk71drbu9LAitDtIxVxz6rSro+A20wVoDw722TUyTZJ8QF1phVxCwvGdWllc4jQ3u4mqc4djvfHu1FJNrSBcXCaM+0h0C9CNzav9u3P1D3f+IqvVddWYBW0c/EshM8UgooXxw4YjLwYTWoDT4u/sFN2fcolHeh1/pPepPyEQYUsu7jCAsEQ/vtagZxgnAeym7muZZJmVvwe4vwkFFOr1eaAYSiwsNuagdJa/rWGDuYl8lMybnU4p/LHvvlf6xNGnRnQ21q6tmcZamaIKav4ZsFUtWE14+3qOkB0q3AFOvev6R4umKdNqrGQoBqLRNqtI0D4Mz1nb10zDKDWsVvQuNES48OTZ6QfaF0V4djxu8qSsaDqEV+TIKuags9V4Jlfbtt004KpoJmi68PWBVnBJEjvOtDdhgnEqzLQc253sjKrqNeePEGrk/OyTqGOP28Igjhde2kTZX7KRfTEzs+OaitJU5KxVNcIeCZgVzIDWuK7+epamHVPkstWDq1Fpzx7DlZFCMwYcRXO4wEkSV+qrk7g/19tEygkBmFLYTE4KLkAd4r0hFZrgUnHX1kJatf4Q4bJRCFlPBopEdUecFZRoX6dEs7132LIdqIOIOamgWpbnhqdlDOttBm0GmvaEx9eXn2RZZXIcXovcBKWmoIwC7+jvWn/remk/etPfMW7PydDteQ5u+fxuxS7HY8KYI6nyEI9Vh9kMpKdGW2zd5E0XEI+PlQRzkrpFAOh9WM1ElxxQGDX6cn+qDjVgcquVcakRr/8BGzukXkk3Sl5UZrbKtR=',
        'bx-umidtoken'       => 'T2gAC5jwrlt8wAZoBR_1-o1doU4TeRnbO8skRNdQAf4wIlu4gy-lxDixGeHpuIn2tLE=',
        'bx-v'               => '2.5.31',
        'content-type'       => 'application/json; charset=UTF-8',
        'sec-ch-ua'          => '"Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"',
        'sec-ch-ua-mobile'   => '?0',
        'sec-ch-ua-platform' => '"Windows"',
        'source'             => 'web',
        'timezone'           => 'Tue Sep 30 2025 14:04:29 GMT+0700',
        'x-accel-buffering'  => 'no',
        'x-request-id'       => 'e83f8601-79ae-4d00-8698-2d5669245939',
        'Cookie'             => 'acw_tc=0a03e54a17592156439043431e1e9e730f2ee3913f4618e19616f0b019d254; _gcl_au=1.1.478265987.1759215648; cna=HW5iIUID2TgCAXEW+ByOr6Ph; _bl_uid=e4mdvg1L67w7zCkbaqgRdII21g5d; xlly_s=1; x-ap=ap-southeast-1; sca=0d9a910c; atpsida=2fe69b3415f54834e08c13f2_1759215866_2; tfstk=gCboLTXHeg-51j3SqWY72uvJcoZxVUTB093ppepU0KJfy9EWpiYF_OxpYusRnpvVC0IJeph5cOCZyaEWJ2YWReyTBPUTN_TB8k1yl_DW3CRuJBur8UT4g-ZDbPUON_S2eMx7WweOOjdpLe-yYjl2hBvEL9-y3EJXOBkyT4PcgK92T08eTqk29CkrY98UisJXTeRPL3PcgKOe8plD3uJ58Z74Ly77sq-XGZAkqd50lVugPQk9B_Jm82WvZ3mRaK0E8ZjnJRSyUlGvdUp5iQXQ54TyxM7Juay48FS1nwxwSkFW46jcdUQ4T0JN2TLVzGcE8sYknHdkDY2DQa1V5E-Y75fVlTC5o6hU8IB93__y-PP6rUJyoI_Lh4Jh0M7JVeMzKp_cgaSh4WiqbQumRIPduDiB4IODBijNDGc3lsa0iSmaO3RXNdF0iDiB4IODBSVmbytyGQ9O.; isg=BCUlFi5aof8TO8WjF5p_udtwNOFfYtn0wx_AyicKKdxrPkew67OSxaWYyLpIefGs; ssxmod_itna=1-Yq0x0Qqmqqu7G0D207DkDwxUxYKGIKDXDUqAQtGgDYq7=GFKDCEoFfr=WbqAKpxE1ejBrwKoh8AY5D/DK4G1DbuPDLDBWYHzK7YvP=K9xWbux8WCe7qddPSEgUcdcl7ExM0kiA6p1gOTGej1HHqe4DHPPDUcGhb7YDxOPD5xDTDWeDGDD3Dmm_DiHoD0KDjmgv_tSIDYPDEmoDaxDbDiWIT4GCiDDCDGFW7G4vKDDzKGjxz7xm=Dk6rpko7D8qc3heO/e3DlctCkiIl9ZyKC86Fw6Ld=oDXctDv=8oAfYK20OfmS8YqlIVhAieYe/2KbGD30qK7=zYeKO53CDLgY=G5e0YeAxSo5xmw1xDiP4NQ0KbbxP4yPXgaXMP_xfqliYMGe=QDdeKdCrpmKzBqGYePjqq7x4zx_QG17A4EDxD; ssxmod_itna2=1-Yq0x0Qqmqqu7G0D207DkDwxUxYKGIKDXDUqAQtGgDYq7=GFKDCEoFfr=WbqAKpxE1ejBrwKoh8AYeDA42dWxqDFrvKElD0veNzmzi8qN7531yD0K0SOf5ob6jTkHkGkAcGMPWcADMGKbxuaZEiGMYakfPYGeEaGU6pK7=pdg6w14taq2o7pWEumWZIP7gEk8QiEv19If3cOziiE123EU7x7GpfGUZiEUO3ATck8UnDx_9Gef4B6GOMDKnQx1OhKPqWGqQvx2=Q7A/MLY_6WjaUIF7LkTOrssHmLZ_OzGZUAblHED_k7uFK_FIdSbHlpuSv7C2htWqjPG7pmlWMYcy/r8DFX7G8DFhWKFl2wSH8niumGY5uOfWVKDW4utRhIWixr0LCEFRBQNw4jweneszpKiFqXoB3nD4fHW4z9WfmG=jqzSrTcBbwTaIm4nDEAG54xx4hxDktRxNlxie_49hxjG7qddOExeD'
    ],
        'json' => [
        'stream' => true,
        'incremental_output' => true,
        'chat_id' => '78af31b1-70cd-4674-98a9-aaa656e590c6',
        'chat_mode' => 'guest',
        'model' => 'qwen3-max',
        'parent_id' => null,
        'messages' => [
            [
                'fid' => 'b563c84a-3027-494b-ab27-16776630af48',
                'parentId' => null,
                'childrenIds' => [
                    '421a9f0a-de32-4b5b-84f9-84deef45bece'
                ],
                'role' => 'user',
                'content' => 'hi',
                'user_action' => 'chat',
                'files' => [],
                'timestamp' => 1759215869,
                'models' => [
                    'qwen3-max'
                ],
                'chat_type' => 't2t',
                'feature_config' => [
                    'thinking_enabled' => false,
                    'output_schema' => 'phase'
                ],
                'extra' => [
                    'meta' => [
                        'subChatType' => 't2t'
                    ]
                ],
                'sub_chat_type' => 't2t',
                'parent_id' => null
            ]
        ],
        'timestamp' => 1759215869
    ]
]);