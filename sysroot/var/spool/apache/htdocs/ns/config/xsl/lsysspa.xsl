<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="xml" omit-xml-declaration="no" indent="yes" />
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>

<xsl:template match="/config">
  <flat-profile>
    <Profile_Rule>http://<xsl:value-of select="$fqdn"/>/init-$MA.cfg</Profile_Rule>
    <Enable_Web_Server>yes</Enable_Web_Server> 
    <User_Password>0000</User_Password>
    <Admin_Passwd>0000</Admin_Passwd>
    <Enable_WAN_Web_Server>yes</Enable_WAN_Web_Server>
    <Resync_Periodic>10</Resync_Periodic>

  <xsl:if test="$model = 'spa8000'">
    <Trunk_Group_1_>1</Trunk_Group_1_>
    <Trunk_Group_2_>1</Trunk_Group_2_>
    <Trunk_Group_3_>1</Trunk_Group_3_>
    <Trunk_Group_4_>1</Trunk_Group_4_>
    <Trunk_Group_5_>1</Trunk_Group_5_> 
    <Trunk_Group_6_>1</Trunk_Group_6_>
    <Trunk_Group_7_>1</Trunk_Group_7_>
    <Trunk_Group_8_>1</Trunk_Group_8_>
  </xsl:if>
</flat-profile>
</xsl:template>
</xsl:stylesheet>
