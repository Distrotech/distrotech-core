<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="is_ext" select="(/config/IP/SysConf/Option[@option='External'] = $iface) and (/config/IP/Dialup/Option[@option='Connection'] != 'ADSL')"/>

<xsl:template match="/config">
  <xsl:if test="not($is_ext)">
    <xsl:call-template name="other"/>
  </xsl:if>
</xsl:template>

<xsl:template name="other">
  <xsl:for-each select="/config/IP/Interfaces/Interface[(@bwout != '') and not(contains(.,':'))]">
    <xsl:variable name="imqcnti" select="position()+5"/>
    <xsl:variable name="ifbwout" select="@bwout"/>
    <xsl:variable name="ifbwin" select="@bwin"/>
    <xsl:if test=". = $iface">
      <xsl:choose>
        <xsl:when test="$imqcnti &lt;= 15">
#Bring up imq Interface
/sbin/ip link set imq<xsl:value-of select="$imqcnti"/> up
/sbin/tc qdisc del dev imq<xsl:value-of select="$imqcnti"/> root > /dev/null 2>&amp;1

#Apply ingress limit
/sbin/tc qdisc add dev imq<xsl:value-of select="$imqcnti"/> root handle 1: htb default 50
/sbin/tc class add dev imq<xsl:value-of select="$imqcnti"/> parent 1: classid 1:1 htb rate <xsl:value-of select="@bwin"/>Kbit
/sbin/tc class add dev imq<xsl:value-of select="$imqcnti"/> parent 1:1 classid 1:50 htb rate <xsl:value-of select="@bwin"/>Kbit ceil <xsl:value-of select="@bwin"/>Kbit
/sbin/tc qdisc add dev imq<xsl:value-of select="$imqcnti"/> parent 1:50 handle 50: sfq perturb 10
<xsl:text>&#xa;</xsl:text>
        </xsl:when>
        <xsl:otherwise>
#Apply ingress limit
#/sbin/tc qdisc del dev <xsl:value-of select="$iface"/> handle ffff: ingress
#/sbin/tc qdisc add dev <xsl:value-of select="$iface"/> handle ffff: ingress
#/sbin/tc filter add dev <xsl:value-of select="$iface"/> parent ffff: protocol ip u32 match ip dst <xsl:value-of select="concat(@nwaddr,'/',@subnet)"/> police rate <xsl:value-of select="@bwout"/>bit burst 1500 drop flowid :50<xsl:text></xsl:text>
        </xsl:otherwise>
      </xsl:choose>#delete egress class
/sbin/tc qdisc del dev <xsl:value-of select="$iface"/> root > /dev/null 2>&amp;1

#Apply egress limit
/sbin/tc qdisc add dev <xsl:value-of select="$iface"/> root handle 1: htb default 50
/sbin/tc class add dev <xsl:value-of select="$iface"/> parent 1: classid 1:1 htb rate <xsl:value-of select="@bwout"/>Kbit
/sbin/tc class add dev <xsl:value-of select="$iface"/> parent 1:1 classid 1:50 htb rate <xsl:value-of select="@bwout"/>Kbit ceil <xsl:value-of select="@bwout"/>Kbit
/sbin/tc qdisc add dev <xsl:value-of select="$iface"/> parent 1:50 handle 50: sfq perturb 10<xsl:text>&#xa;</xsl:text>
      <xsl:for-each select="/config/IP/FW/Iface[@iface=$iface]/Source">
        <xsl:variable name="classcnt" select="position()+50"/>
        <xsl:if test="(@bwin != '') or (@bwout != '')">
#Limit Data For <xsl:value-of select="concat(@name,' ',$iface)"/> to <xsl:value-of select="@bwout"/>kbs
/sbin/tc class add dev <xsl:value-of select="$iface"/> parent 1:1 classid 1:<xsl:value-of select="$classcnt"/> htb rate <xsl:value-of select="@bwout"/>Kbit ceil <xsl:value-of select="$ifbwout"/>Kbit
/sbin/tc qdisc add dev <xsl:value-of select="$iface"/> parent 1:<xsl:value-of select="$classcnt"/> handle <xsl:value-of select="$classcnt"/>: sfq perturb 10
/sbin/tc filter add dev <xsl:value-of select="$iface"/> parent 1: protocol ip prio <xsl:value-of select="position()"/> u32 match ip dst <xsl:value-of select="concat(@ipaddr,'/',@subnet)"/> flowid 1:<xsl:value-of select="$classcnt"/><xsl:text>&#xa;</xsl:text>
          <xsl:choose>
            <xsl:when test="$imqcnti &lt;= 15">
#Limit Data For <xsl:value-of select="concat(@name,' imq',$imqcnti)"/> to <xsl:value-of select="@bwin"/>kbs
/sbin/tc class add dev imq<xsl:value-of select="$imqcnti"/> parent 1:1 classid 1:<xsl:value-of select="$classcnt"/> htb rate <xsl:value-of select="@bwin"/>Kbit ceil <xsl:value-of select="$ifbwin"/>Kbit
/sbin/tc qdisc add dev imq<xsl:value-of select="$imqcnti"/> parent 1:<xsl:value-of select="$classcnt"/> handle <xsl:value-of select="$classcnt"/>: sfq perturb 10
/sbin/tc filter add dev imq<xsl:value-of select="$imqcnti"/> parent 1: protocol ip prio <xsl:value-of select="position()"/> u32 match ip src <xsl:value-of select="concat(@ipaddr,'/',@subnet)"/> flowid 1:<xsl:value-of select="$classcnt"/><xsl:text>&#xa;</xsl:text>
            </xsl:when>
            <xsl:otherwise>
/sbin/tc filter add dev <xsl:value-of select="$iface"/> parent ffff: protocol ip prio <xsl:value-of select="position()"/> u32 match ip src <xsl:value-of select="concat(@ipaddr,'/',@subnet)"/> flowid 1:<xsl:value-of select="$classcnt"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:if>
      </xsl:for-each>
    </xsl:if>
  </xsl:for-each>
</xsl:template>
</xsl:stylesheet>
