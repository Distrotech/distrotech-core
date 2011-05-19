<%
function ciscoxml($exten,$pass,$servip,$macaddr) {
  $config="<device>
	<deviceProtocol>SIP</deviceProtocol>
	<sshUserId>admin</sshUserId>
	<sshPassword>password</sshPassword>
	<devicePool>
		<dateTimeSetting>
			<dateTemplate>D/M/YA</dateTemplate>
			<timeZone>South Africa Standard Time</timeZone>
			<ntps>
				<ntp>
					<name>" . $servip . "</name>
					<ntpMode>Unicast</ntpMode>
				</ntp>
			</ntps>
		</dateTimeSetting>
		<callManagerGroup>
			<members>
				<member priority=\"0\">
					<callManager>
						<ports>
							<ethernetPhonePort>2000</ethernetPhonePort>
							<sipPort>5060</sipPort>
							<securedSipPort>5061</securedSipPort>
						</ports>
						<processNodeName>" . $servip . "</processNodeName>
					</callManager>
				</member>
			</members>
		</callManagerGroup>
		<srstInfo>
			<name>Disable</name>
			<srstOption>Disable</srstOption>
			<userModifiable>true</userModifiable>
			<port1>2000</port1>
			<port2>2000</port2>
			<port3>2000</port3>
			<sipPort1>5060</sipPort1>
			<sipPort2>5060</sipPort2>
			<sipPort3>5060</sipPort3>
			<isSecure>false</isSecure>
		</srstInfo>
		<mlppDomainId>-1</mlppDomainId>
		<mlppIndicationStatus>Default</mlppIndicationStatus>
		<preemption>Default</preemption>
		<connectionMonitorDuration>120</connectionMonitorDuration>
	</devicePool>
	<sipProfile>
		<sipProxies> 
			<registerWithProxy>true</registerWithProxy> 
		</sipProxies>
		<sipCallFeatures>
			<cnfJoinEnabled>true</cnfJoinEnabled>
			<callForwardURI>x-cisco-serviceuri-cfwdall</callForwardURI>
			<callPickupURI>x-cisco-serviceuri-pickup</callPickupURI>
			<callPickupListURI>x-cisco-serviceuri-opickup</callPickupListURI>
			<callPickupGroupURI>x-cisco-serviceuri-gpickup</callPickupGroupURI>
			<meetMeServiceURI>x-cisco-serviceuri-meetme</meetMeServiceURI>
			<abbreviatedDialURI>x-cisco-serviceuri-abbrdial</abbreviatedDialURI>
			<rfc2543Hold>false</rfc2543Hold>
			<callHoldRingback>2</callHoldRingback>
			<localCfwdEnable>true</localCfwdEnable>
			<semiAttendedTransfer>true</semiAttendedTransfer>
			<anonymousCallBlock>2</anonymousCallBlock>
			<callerIdBlocking>2</callerIdBlocking>
			<dndControl>0</dndControl>
			<remoteCcEnable>true</remoteCcEnable>
		</sipCallFeatures>
		<sipStack>
			<sipInviteRetx>6</sipInviteRetx>
			<sipRetx>10</sipRetx>
			<timerInviteExpires>180</timerInviteExpires>
			<timerRegisterExpires>3600</timerRegisterExpires>
			<timerRegisterDelta>5</timerRegisterDelta>
			<timerKeepAliveExpires>120</timerKeepAliveExpires>
			<timerSubscribeExpires>120</timerSubscribeExpires>
			<timerSubscribeDelta>5</timerSubscribeDelta>
			<timerT1>500</timerT1>
			<timerT2>4000</timerT2>
			<maxRedirects>70</maxRedirects>
			<remotePartyID>false</remotePartyID>
			<userInfo>None</userInfo>
		</sipStack>
		<autoAnswerTimer>1</autoAnswerTimer>
		<autoAnswerAltBehavior>false</autoAnswerAltBehavior>
		<autoAnswerOverride>true</autoAnswerOverride>
		<transferOnhookEnabled>false</transferOnhookEnabled>
		<enableVad>false</enableVad>
		<preferredCodec>g729a</preferredCodec>
		<dtmfAvtPayload>101</dtmfAvtPayload>
		<dtmfDbLevel>3</dtmfDbLevel>
		<dtmfOutofBand>avt</dtmfOutofBand>
		<alwaysUsePrimeLine>false</alwaysUsePrimeLine>
		<alwaysUsePrimeLineVoiceMail>false</alwaysUsePrimeLineVoiceMail>
		<kpml>3</kpml>
		<stutterMsgWaiting>1</stutterMsgWaiting>
		<callStats>false</callStats>
		<silentPeriodBetweenCallWaitingBursts>10</silentPeriodBetweenCallWaitingBursts>
		<disableLocalSpeedDialConfig>false</disableLocalSpeedDialConfig>
		<phoneLabel>" . $exten . "</phoneLabel>
		<sipLines>
			<line button=\"1\">
				<featureID>9</featureID>
				<featureLabel>" . $exten . "</featureLabel>
				<name>" . $exten . "</name>
				<displayName>" . $exten . "</displayName>
				<authName>" . $exten . "</authName>
				<authPassword>" . $pass . "</authPassword>
				<contact>" . $exten . "</contact>
				<proxy>" . $servip  . "</proxy>
				<port>5060</port>
				<autoAnswer>
					<autoAnswerEnabled>2</autoAnswerEnabled>
				</autoAnswer>
				<callWaiting>3</callWaiting>
				<sharedLine>false</sharedLine>
				<messageWaitingLampPolicy>1</messageWaitingLampPolicy>
				<messagesNumber>100</messagesNumber>
				<ringSettingIdle>4</ringSettingIdle>
				<ringSettingActive>5</ringSettingActive>
				<forwardCallInfoDisplay>
					<callerName>true</callerName>
					<callerNumber>false</callerNumber>
					<redirectedNumber>false</redirectedNumber>
					<dialedNumber>true</dialedNumber>
				</forwardCallInfoDisplay>
			</line>
		</sipLines>
		<voipControlPort>5060</voipControlPort>
		<startMediaPort>16384</startMediaPort>
		<stopMediaPort>32766</stopMediaPort>
		<dscpForAudio>184</dscpForAudio>
		<ringSettingBusyStationPolicy>0</ringSettingBusyStationPolicy>
		<dialTemplate>dialplan.xml</dialTemplate>
	</sipProfile>
	<commonProfile>
		<backgroundImageAccess>true</backgroundImageAccess>
		<callLogBlfEnabled>2</callLogBlfEnabled>
	</commonProfile>
	<loadInformation>SIP11.8-3-1S</loadInformation>
	<vendorConfig>
		<disableSpeaker>false</disableSpeaker>
		<disableSpeakerAndHeadset>false</disableSpeakerAndHeadset>
		<pcPort>0</pcPort>
		<settingsAccess>1</settingsAccess>
		<garp>0</garp>
		<voiceVlanAccess>0</voiceVlanAccess>
		<videoCapability>0</videoCapability>
		<autoSelectLineEnable>0</autoSelectLineEnable>
		<webAccess>0</webAccess>
		<displayOnTime>00:00</displayOnTime>
		<displayOnDuration>00:00</displayOnDuration>
		<displayIdleTimeout>00:00</displayIdleTimeout>
		<spanToPCPort>1</spanToPCPort>
		<loggingDisplay>1</loggingDisplay>
	</vendorConfig>
	<userLocale>
		<langCode>en_US</langCode>
		<version>1.0.0.0-1</version>
		<winCharSet>iso-8859-1</winCharSet>
	</userLocale>
	<networkLocaleInfo>
		<version>1.0.0.0-1</version>
	</networkLocaleInfo>
	<deviceSecurityMode>1</deviceSecurityMode>
	<authenticationURL>http://" .  $servip . "/services/authenticate.php</authenticationURL>
	<directoryURL>http://" . $servip . "/services/directory.php</directoryURL>
	<servicesURL>http://" . $servip . "/services/menu.xml</servicesURL>
	<idleTimeout>0</idleTimeout>
	<dscpForSCCPPhoneConfig>96</dscpForSCCPPhoneConfig>
	<dscpForSCCPPhoneServices>0</dscpForSCCPPhoneServices>
	<dscpForCm2Dvce>96</dscpForCm2Dvce>
	<transportLayerProtocol>4</transportLayerProtocol>
	<capfAuthMode>0</capfAuthMode>
	<capfList>
		<capf>
			<phonePort>3804</phonePort>
		</capf>
	</capfList>
	<encrConfig>false</encrConfig>
</device>\n";
  file_put_contents("/tftpboot/SEP" . $macaddr . ".cnf.xml",$config);
}
%>
