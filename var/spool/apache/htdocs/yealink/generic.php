[ autop_mode ]
path = /config/Setting/autop.cfg
mode = 6
schedule_min = 240

[ PNP ]
path = /config/Setting/autop.cfg
Pnp = 0

[ autoprovision ]
path = /config/Setting/autop.cfg
server_address = http://pbx.distrotech.co.za/${mac}.cfg

[ account ]
path = /config/voip/sipAccount0.cfg
Enable = 1
SIPServerHost = distrotech.co.za
SIPServerPort = 5060
UseOutboundProxy = 0
SubsribeRegister = 1
SubsribeMWI = 1
dialoginfo_callpickup = 1

[ NAT ]
path = /config/voip/sipAccount0.cfg
EnableUDPUpdate = 1

[ Time ]
path = /config/Setting/Setting.cfg
TimeZone = +2
TimeServer1 = pbx.distrotech.co.za
TimeServer2 = pbx.distrotech.co.za
SummerTime = 0
