<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">[modules]
autoload=yes

;Make sure the db bits are loaded.
preload => res_config.so
preload => res_odbc.so
preload => res_config_odbc.so
preload => chan_local.so
preload => chan_sip.so
preload => chan_iax.so
preload => chan_dahdi.so

;Fax Changes
noload => app_faxdetect.so

;Old Presence Crap
noload => app_adndelivered.so
noload => app_pogpbridge.so

;Asterisk 1.8
noload => chan_h323.so
noload => cel_custom.so
noload => cel_manager.so
noload => cel_odbc.so
noload => cel_pgsql.so
noload => cel_sqlite3_custom.so
noload => cdr_syslog.so
noload => cdr_mysql.so
noload => res_calendar.so
noload => app_fax.so
noload => app_faxgateway.so

;Asterisk 1.6
noload => res_timing_timerfd.so
noload => res_timing_pthread.so
noload => res_phoneprov.so
noload => test_dlinklists.so
noload => res_config_ldap.so
noload => chan_jingle.so
noload => app_minivm.so
noload => chan_usbradio.so
noload => chan_unistim.so
noload => cdr_sqlite3_custom.so
noload => cdr_odbc.so
<xsl:choose>
  <xsl:when test="$woomera = '1'">
    <xsl:text>load => chan_woomera.so</xsl:text>
  </xsl:when>
  <xsl:otherwise>
    <xsl:text>noload => chan_woomera.so</xsl:text>
  </xsl:otherwise>
</xsl:choose>
noload => chan_zap.so
noload => app_zapbarge.so
noload => app_zapras.so
noload => app_zapscan.so
noload => app_zapateller.so
noload => codec_zap.so

;Load g723
<xsl:choose>
  <xsl:when test="$useg723">
    <xsl:text>load => codec_g723.so</xsl:text>
  </xsl:when>
  <xsl:otherwise>
    <xsl:text>noload => codec_g723.so</xsl:text>
  </xsl:otherwise>
</xsl:choose>

;Load g729
<xsl:choose>
  <xsl:when test="$useg729">
    <xsl:text>load => codec_g729.so</xsl:text>
  </xsl:when>
  <xsl:otherwise>
    <xsl:text>noload => codec_g729.so</xsl:text>
  </xsl:otherwise>
</xsl:choose>

;Disable mISDN V1 Module Bellow
<xsl:choose>
  <xsl:when test="$misdn = '1'">
    <xsl:text>load => chan_misdn.so</xsl:text>
  </xsl:when>
  <xsl:otherwise>
    <xsl:text>noload => chan_misdn.so</xsl:text>
  </xsl:otherwise>
</xsl:choose>
;Disable mISDN V2 Module Bellow
noload => chan_lcr.so
;Disable ISDN Modules Bellow
noload => chan_capi.so
noload => app_capiCD.so
noload => app_capiHOLD.so
noload => app_capiRETRIEVE.so
noload => app_capiECT.so
noload => app_capiMCID.so

;Disable ALSA
noload => chan_alsa.so
;Disable Some Modules Not Used
noload => res_config_pgsql.so
noload => pbx_ael.so
noload => app_txfax.so
noload => app_rxfax.so
noload => app_skel.so
noload => app_ivrdemo.so
noload => app_devstate.so
noload => func_netsentry.so
noload => res_jabber.so
noload => chan_gtalk.so

;Disable Old Depricated Modules
noload => app_muxmon.so

;Disable OGG Vorbis
noload => format_ogg_vorbis.so

;Disable Will Call u
noload => pbx_wilcalu.so

;Disable Q Call
noload => app_qcall.so

;Disable PGSQL CDR
noload => cdr_pgsql.so

;Disable Phone Module
noload => chan_phone.so

;Disable GUI Console
noload => pbx_gtkconsole.so
noload => pbx_kdeconsole.so

;Disable Intercom
noload => app_intercom.so

;Disable Skinny and mgcp
noload => chan_skinny.so
noload => chan_mgcp.so

;Dont load modem channels
noload => chan_modem.so
noload => chan_modem_aopen.so
noload => chan_modem_bestdata.so
noload => chan_modem_i4l.so

;disable old modules
noload => func_uri.so

[global]
</xsl:template>
</xsl:stylesheet>
