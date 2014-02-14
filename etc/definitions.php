<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et :
/*
 * Copyright (C) 2009  Bruno PrÃ©mont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *
 * Most RRD Graph definitions copied from collection.cgi
 */
$GraphDefs     = array();
$MetaGraphDefs = array();

if (is_file('etc/definitions.local.php'))
require_once('etc/definitions.local.php');

function load_graph_definitions($logarithmic = false, $tinylegend = false, $zero = false) {
    global $GraphDefs, $MetaGraphDefs;

    $Canvas   = 'FFFFFF';

    $FullRed    = 'FF0000';
    $FullGreen  = '00E000';
    $FullBlue   = '0000FF';
    $FullYellow = 'F0A000';
    $FullCyan   = '00A0FF';
    $FullMagenta= 'A000FF';

    $HalfRed    = 'F7B7B7';
    $HalfGreen  = 'B7EFB7';
    $HalfBlue   = 'B7B7F7';
    $HalfYellow = 'F3DFB7';
    $HalfCyan   = 'B7DFF7';
    $HalfMagenta= 'DFB7F7';

    $HalfBlueGreen = '89B3C9';

    $GraphDefs = array();
    $GraphDefs['apache_bytes'] = array(
            '-v', 'Bits/s',
            'DEF:avg_raw={file}:count:AVERAGE',
            'CDEF:avg=avg_raw,8,*',
            'CDEF:mytime=avg_raw,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:avg_sample=avg_raw,UN,0,avg_raw,IF,sample_len,*',
            'CDEF:avg_sum=PREV,UN,0,PREV,IF,avg_sample,+',
            "AREA:avg#$HalfBlue",
            "LINE1:avg#$FullBlue:Bit/s",
            'GPRINT:avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg:LAST:%5.1lf%s Last',
            'GPRINT:avg_sum:LAST:(ca. %5.1lf%sB Total)\l');
    $GraphDefs['apache_requests'] = array(
            '-v', 'Requests/s',
            'DEF:avg={file}:count:AVERAGE',
            "LINE1:avg#$FullBlue:Requests/s",
            'GPRINT:avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:avg:LAST:%6.2lf Last');
    $GraphDefs['apache_scoreboard'] = array(
            'DEF:avg={file}:count:AVERAGE',
            "LINE1:avg#$FullBlue:Processes",
            'GPRINT:avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:avg:LAST:%6.2lf Last');
    $GraphDefs['bitrate'] = array(
            '-v', 'Bits/s',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Bits/s",
            'GPRINT:avg:AVERAGE:%5.1lf%s Average,',
            'GPRINT:avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['charge'] = array(
            '-v', 'Ah',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Charge",
            'GPRINT:avg:AVERAGE:%5.1lf%sAh Avg,',
            'GPRINT:avg:LAST:%5.1lf%sAh Last\l');
    $GraphDefs['counter'] = array(
            '-v', 'Events',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Percent",
            'GPRINT:avg:AVERAGE:%6.2lf%% Avg,',
            'GPRINT:avg:LAST:%6.2lf%% Last\l');
    $GraphDefs['cpu'] = array(
            '-v', 'CPU load',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Percent",
            'GPRINT:avg:AVERAGE:%6.2lf%% Avg,',
            'GPRINT:avg:LAST:%6.2lf%% Last\l');
    $GraphDefs['current'] = array(
            '-v', 'Watt',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Power",
            'GPRINT:avg:AVERAGE:%5.1lf%sW Avg,',
            'GPRINT:avg:LAST:%5.1lf%sW Last\l');

    $GraphDefs['current'] = array(
            '-v', 'Watt',
            'DEF:avg_raw={file}:value:AVERAGE',
            'CDEF:avg=avg_raw,3600,/',
            'CDEF:mytime=avg_raw,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:avg_sample=avg,UN,0,avg,IF,sample_len,*',
            'CDEF:avg_sum=PREV,UN,0,PREV,IF,avg_sample,+',
            'CDEF:price=avg_sum,1000,/,0.15,*',
            "LINE1:avg_raw#$FullBlue:Bits/s",
            'GPRINT:avg_raw:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg_raw:LAST:%5.1lf%s Last',
            'GPRINT:avg_sum:LAST:(%.1lf%sW/h',
                'GPRINT:price:LAST:%.1lf Euros)\l');

    $GraphDefs['percentidtplx'] = array(
            '-v', 'Percent', '-r', '-l', '0', '-u', '100',
            'DEF:avg_AC={pathplugin}current-AC Avg Power power_management_board (21.0).rrd:value:AVERAGE',
            'DEF:avg_A={pathplugin}current-Domain A AvgPwr power_management_board (21.0).rrd:value:AVERAGE',
            'DEF:avg_B={pathplugin}current-Domain B AvgPwr power_management_board (21.0).rrd:value:AVERAGE',
            'DEF:avg_FAN={pathplugin}current-Chassis Fan Pwr power_management_board (21.0).rrd:value:AVERAGE',
            'CDEF:avg_rawa=avg_FAN,avg_A,-',
            'CDEF:avg_rawb=avg_rawa,avg_B,-',
            'CDEF:avg=0,avg_rawb,-,avg_AC,/,100,*',
            "LINE1:avg#$FullBlue:Percent",
            'GPRINT:avg:AVERAGE:%5.1lf%% Avg,',
            'GPRINT:avg:LAST:%5.1lf%% Last\l');
    $GraphDefs['currentidtplx'] = array(
            '-v', 'Watt',
            'DEF:avg_AC={pathplugin}current-AC Avg Power power_management_board (21.0).rrd:value:AVERAGE',
            'DEF:avg_A={pathplugin}current-Domain A AvgPwr power_management_board (21.0).rrd:value:AVERAGE',
            'DEF:avg_B={pathplugin}current-Domain B AvgPwr power_management_board (21.0).rrd:value:AVERAGE',
            'DEF:avg_FAN={pathplugin}current-Chassis Fan Pwr power_management_board (21.0).rrd:value:AVERAGE',
            'CDEF:avg_rawa=avg_AC,avg_A,-',
            'CDEF:avg_rawb=avg_rawa,avg_B,-',
            'CDEF:avg_raw=avg_rawb,avg_FAN,-',
            'CDEF:avg=avg_raw,3600,/',
            'CDEF:mytime=avg_raw,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:avg_sample=avg,UN,0,avg,IF,sample_len,*',
            'CDEF:avg_sum=PREV,UN,0,PREV,IF,avg_sample,+',
            'CDEF:price=avg_sum,1000,/,0.15,*',
            "LINE1:avg_raw#$FullBlue:Bits/s",
            'GPRINT:avg_raw:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg_raw:LAST:%5.1lf%s Last',
            'GPRINT:avg_sum:LAST:(%.1lf%sW/h',
                'GPRINT:price:LAST:%.1lf Euros)\l');

    $GraphDefs['df'] = array(
            '-v', 'Percent', '-l', '0',
            'DEF:free_avg={file}:free:AVERAGE',
            'DEF:used_avg={file}:used:AVERAGE',
            'CDEF:total=free_avg,used_avg,+',
            'CDEF:free_pct=100,free_avg,*,total,/',
            'CDEF:used_pct=100,used_avg,*,total,/',
            'CDEF:free_acc=free_pct,used_pct,+',
            'CDEF:used_acc=used_pct',
            "AREA:free_acc#$HalfGreen",
            "AREA:used_acc#$HalfRed",
            "LINE1:free_acc#$FullGreen:Free",
            'GPRINT:free_avg:AVERAGE:%5.1lf%sB Avg,',
            'GPRINT:free_avg:LAST:%5.1lf%sB Last\l',
            "LINE1:used_acc#$FullRed:Used",
            'GPRINT:used_avg:AVERAGE:%5.1lf%sB Avg,',
            'GPRINT:used_avg:LAST:%5.1lf%sB Last\l');
    $GraphDefs['df_complex'] = array(
            '-b', '1024', '-v', 'Bytes' ,'--units=si',
            'DEF:free_avg={pathplugin}df_complex-free.rrd:value:AVERAGE',
            //        'CDEF:free_nnl=free_avg,UN,0,free_avg,IF',
            'DEF:used_avg={pathplugin}df_complex-used.rrd:value:AVERAGE',
            //        'CDEF:used_nnl=used_avg,UN,0,used_avg,IF',
            'DEF:reserved_avg={pathplugin}df_complex-reserved.rrd:value:AVERAGE',
            //        'CDEF:reserved_nnl=reserved_avg,UN,0,reserved_avg,IF',
            'CDEF:reserved_stk=reserved_avg',
            'CDEF:used_stk=used_avg,reserved_stk,+',
            'CDEF:free_stk=free_avg,used_stk,+',
            'CDEF:free_stk2x=free_stk,100,*',
            'VDEF:max_free_stk=free_stk2x,MAXIMUM',
            'VDEF:max_free_stk2=free_stk,MAXIMUM',
            'VDEF:total=free_stk,LAST',
            'VDEF:Du=used_stk,LSLSLOPE',
            'VDEF:Hu=used_stk,LSLINT',
            'VDEF:Cu=used_stk,LSLCORREL',
            'CDEF:avgu=used_stk,POP,Hu,Du,COUNT,*,+',
            'CDEF:avgul=avgu,0,max_free_stk,LIMIT',
            'CDEF:out=max_free_stk2,avgul,LT,avgul,UNKN,IF',
            'VDEF:minout=out,FIRST',
            "AREA:out#$FullRed:Out of space on",
            'GPRINT:minout:%c\l:strftime',
            "AREA:free_stk#$HalfGreen",
            "LINE1:free_stk#$FullGreen:free    ",
            "HRULE:total#$FullGreen::dashes",
            "GPRINT:free_avg:AVERAGE:%5.1lf%s Avg,",
            "GPRINT:free_avg:LAST:%5.1lf%s Last\l",
            "AREA:used_stk#bfbfff",
            "LINE1:used_stk#0000ff:used    ",
            "GPRINT:used_avg:AVERAGE:%5.1lf%s Avg,",
            "GPRINT:used_avg:LAST:%5.1lf%s Last\l",
            "AREA:reserved_stk#ffebbf",
            "LINE1:reserved_stk#ffb000:reserved",
            "GPRINT:reserved_avg:AVERAGE:%5.1lf%s Avg,",
            "GPRINT:reserved_avg:LAST:%5.1lf%s Last\l",
            "LINE1:avgul#$FullMagenta:usage trend until free space\l",
            );
    $GraphDefs['disk'] = array(
            'DEF:rtime_avg={file}:rtime:AVERAGE',
            'DEF:wtime_avg={file}:wtime:AVERAGE',
            'CDEF:rtime_avg_ms=rtime_avg,1000,/',
            'CDEF:wtime_avg_ms=wtime_avg,1000,/',
            'CDEF:total_avg_ms=rtime_avg_ms,wtime_avg_ms,+',
            'CDEF:reverse_wtime_avg_ms=0,wtime_avg_ms,-',
            "LINE1:reverse_wtime_avg_ms#$FullGreen:Write",
            'GPRINT:wtime_avg_ms:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:wtime_avg_ms:LAST:%5.1lf%s Last\n',
            "LINE1:rtime_avg_ms#$FullBlue:Read ",
            'GPRINT:rtime_avg_ms:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:rtime_avg_ms:LAST:%5.1lf%s Last\n',
            "LINE1:total_avg_ms#$FullRed:Total",
            'GPRINT:total_avg_ms:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:total_avg_ms:LAST:%5.1lf%s Last');
    $GraphDefs['disk_octets'] = array(
            '-v', 'Bytes/s', '--units=si',
            'DEF:out_avg={file}:write:AVERAGE',
            'DEF:inc_avg={file}:read:AVERAGE',
            'CDEF:reverse_out_avg=0,out_avg,-',
            'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
            'CDEF:mytime=out_avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:out_avg_sample=out_avg,UN,0,out_avg,IF,sample_len,*',
            'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
            'CDEF:inc_avg_sample=inc_avg,UN,0,inc_avg,IF,sample_len,*',
            'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
            "AREA:reverse_out_avg#$HalfGreen",
            "AREA:inc_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:reverse_out_avg#$FullGreen:Written",
            'GPRINT:out_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:out_avg:LAST:%5.1lf%s Last',
            'GPRINT:out_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
            "LINE1:inc_avg#$FullBlue:Read   ",
            'GPRINT:inc_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:inc_avg:LAST:%5.1lf%s Last',
            'GPRINT:inc_avg_sum:LAST:(ca. %5.1lf%sB Total)\l');
    $GraphDefs['disk_merged'] = array(
            '-v', 'Merged Ops/s', '--units=si',
            'DEF:out_avg={file}:write:AVERAGE',
            'DEF:inc_avg={file}:read:AVERAGE',
            'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
            'CDEF:reverse_out_avg=0,out_avg,-',
            "AREA:reverse_out_avg#$HalfGreen",
            "AREA:inc_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:reverse_out_avg#$FullGreen:Written",
            'GPRINT:out_avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:out_avg:LAST:%6.2lf Last\l',
            "LINE1:inc_avg#$FullBlue:Read   ",
            'GPRINT:inc_avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:inc_avg:LAST:%6.2lf Last\l');
    $GraphDefs['disk_ops'] = array(
            '-v', 'Ops/s', '--units=si',
            'DEF:out_avg={file}:write:AVERAGE',
            'DEF:inc_avg={file}:read:AVERAGE',
            'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
            'CDEF:reverse_out_avg=0,out_avg,-',
            "AREA:reverse_out_avg#$HalfGreen",
            "AREA:inc_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:reverse_out_avg#$FullGreen:Written",
            'GPRINT:out_avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:out_avg:LAST:%6.2lf Last\l',
            "LINE1:inc_avg#$FullBlue:Read   ",
            'GPRINT:inc_avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:inc_avg:LAST:%6.2lf Last\l');
    $GraphDefs['disk_time'] = array(
            '-v', 'Seconds',
            'DEF:out_avg_raw={file}:write:AVERAGE',
            'DEF:inc_avg_raw={file}:read:AVERAGE',
            'CDEF:out_avg=out_avg_raw,16.666,/',
            'CDEF:inc_avg=inc_avg_raw,16.666,/',
            'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
            'CDEF:reverse_out_avg=0,out_avg,-',
            "AREA:reverse_out_avg#$HalfGreen",
            "AREA:inc_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:reverse_out_avg#$FullGreen:Written",
            'GPRINT:out_avg:AVERAGE:%5.1lf%ss Avg,',
            'GPRINT:out_avg:LAST:%5.1lf%ss Last\l',
            "LINE1:inc_avg#$FullBlue:Read   ",
            'GPRINT:inc_avg:AVERAGE:%5.1lf%ss Avg,',
            'GPRINT:inc_avg:LAST:%5.1lf%ss Last\l');
    $GraphDefs['dns_traffic'] = array(
            'DEF:rsp_avg_raw={file}:responses:AVERAGE',
            'DEF:qry_avg_raw={file}:queries:AVERAGE',
            'CDEF:rsp_avg=rsp_avg_raw,8,*',
            'CDEF:qry_avg=qry_avg_raw,8,*',
            'CDEF:overlap=rsp_avg,qry_avg,GT,qry_avg,rsp_avg,IF',
            'CDEF:mytime=rsp_avg_raw,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:rsp_avg_sample=rsp_avg_raw,UN,0,rsp_avg_raw,IF,sample_len,*',
            'CDEF:rsp_avg_sum=PREV,UN,0,PREV,IF,rsp_avg_sample,+',
            'CDEF:qry_avg_sample=qry_avg_raw,UN,0,qry_avg_raw,IF,sample_len,*',
            'CDEF:qry_avg_sum=PREV,UN,0,PREV,IF,qry_avg_sample,+',
            "AREA:rsp_avg#$HalfGreen",
            "AREA:qry_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:rsp_avg#$FullGreen:Responses",
            'GPRINT:rsp_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:rsp_avg:LAST:%5.1lf%s Last',
            'GPRINT:rsp_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
            "LINE1:qry_avg#$FullBlue:Queries  ",
            'GPRINT:qry_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:qry_avg:LAST:%5.1lf%s Last',
            'GPRINT:qry_avg_sum:LAST:(ca. %5.1lf%sB Total)\l');
    $GraphDefs['email_count'] = array(
            '-v', 'Mails',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullMagenta:Count ",
            'GPRINT:avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:avg:LAST:%4.1lf Last\l');
    $GraphDefs['files'] = $GraphDefs['email_count'];
    $GraphDefs['email_size'] = array(
            '-v', 'Bytes',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullMagenta:Count ",
            'GPRINT:avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:avg:LAST:%4.1lf Last\l');
    $GraphDefs['bytes'] = $GraphDefs['email_size'];
    $GraphDefs['spam_score'] = array(
            '-v', 'Score',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Score ",
            'GPRINT:avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:avg:LAST:%4.1lf Last\l');
    $GraphDefs['spam_check'] = array(
            'DEF:avg={file}:hits:AVERAGE',
            "LINE1:avg#$FullMagenta:Count ",
            'GPRINT:avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:avg:LAST:%4.1lf Last\l');
    $GraphDefs['conntrack'] = array(
            '-v', 'Entries',
            'DEF:avg={file}:entropy:AVERAGE',
            "LINE1:avg#$FullBlue:Count",
            'GPRINT:avg:AVERAGE:%4.0lf Avg,',
            'GPRINT:avg:LAST:%4.0lf Last\l');
    $GraphDefs['entropy'] = array(
            '-v', 'Bits',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Bits",
            'GPRINT:avg:AVERAGE:%4.0lfbit Avg,',
            'GPRINT:avg:LAST:%4.0lfbit Last\l');
    $GraphDefs['fanspeed'] = array(
            '-v', 'RPM',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullMagenta:RPM",
            'GPRINT:avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:avg:LAST:%4.1lf Last\l');
    $GraphDefs['frequency'] = array(
            '-v', 'Hertz',
            'DEF:avg={file}:frequency:AVERAGE',
            "LINE1:avg#$FullBlue:Frequency [Hz]",
            'GPRINT:avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:avg:LAST:%4.1lf Last\l');
    $GraphDefs['frequency_offset'] = array( // NTPd
            'DEF:ppm_avg={file}:value:AVERAGE',
            "LINE1:ppm_avg#$FullBlue:{inst}",
            'GPRINT:ppm_avg:AVERAGE:%5.2lf Avg,',
            'GPRINT:ppm_avg:LAST:%5.2lf Last');
    $GraphDefs['gauge'] = array(
            '-v', ' ',
            'DEF:temp_avg={file}:value:AVERAGE',
            "LINE1:temp_avg#$FullBlue: ",
            'GPRINT:temp_avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:temp_avg:LAST:%6.2lf Last\l');
    $GraphDefs['hddtemp'] = array(
            '-v', 'Â°C',
            'DEF:temp_avg={file}:value:AVERAGE',
            "LINE1:temp_avg#$FullRed:Temperature",
            'GPRINT:temp_avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:temp_avg:LAST:%4.1lf Last\l');
    $GraphDefs['humidity'] = array(
            '-v', 'Percent',
            'DEF:temp_avg={file}:value:AVERAGE',
            "LINE1:temp_avg#$FullGreen:Temperature",
            'GPRINT:temp_avg:AVERAGE:%4.1lf%% Avg,',
            'GPRINT:temp_avg:LAST:%4.1lf%% Last\l');
    $GraphDefs['if_errors'] = array(
            '-v', 'Errors/s', '--units=si',
            'DEF:tx_avg={file}:tx:AVERAGE',
            'DEF:rx_avg={file}:rx:AVERAGE',
            'CDEF:overlap=tx_avg,rx_avg,GT,rx_avg,tx_avg,IF',
            'CDEF:mytime=tx_avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:tx_avg_sample=tx_avg,UN,0,tx_avg,IF,sample_len,*',
            'CDEF:tx_avg_sum=PREV,UN,0,PREV,IF,tx_avg_sample,+',
            'CDEF:rx_avg_sample=rx_avg,UN,0,rx_avg,IF,sample_len,*',
            'CDEF:rx_avg_sum=PREV,UN,0,PREV,IF,rx_avg_sample,+',
            'CDEF:reverse_tx_avg=0,tx_avg,-',
            "AREA:tx_avg#$HalfGreen",
            "AREA:reverse_tx_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:reverse_tx_avg#$FullGreen:Outgoing",
            'GPRINT:tx_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:tx_avg:LAST:%5.1lf%s Last',
            'GPRINT:tx_avg_sum:LAST:(ca. %4.0lf%s Total)\l',
            "LINE1:rx_avg#$FullBlue:Incoming",
            'GPRINT:rx_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:rx_avg:LAST:%5.1lf%s Last',
            'GPRINT:rx_avg_sum:LAST:(ca. %4.0lf%s Total)\l');
    $GraphDefs['if_collisions'] = array(
            '-v', 'Collisions/s', '--units=si',
            'DEF:avg_raw={file}:value:AVERAGE',
            'CDEF:avg=avg_raw,8,*',
            "LINE1:avg#$FullBlue:Collisions/s",
            'GPRINT:avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['if_dropped'] = array(
            '-v', 'Packets/s', '--units=si',
            'DEF:tx_avg={file}:tx:AVERAGE',
            'DEF:rx_avg={file}:rx:AVERAGE',
            'CDEF:overlap=tx_avg,rx_avg,GT,rx_avg,tx_avg,IF',
            'CDEF:mytime=tx_avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:tx_avg_sample=tx_avg,UN,0,tx_avg,IF,sample_len,*',
            'CDEF:tx_avg_sum=PREV,UN,0,PREV,IF,tx_avg_sample,+',
            'CDEF:rx_avg_sample=rx_avg,UN,0,rx_avg,IF,sample_len,*',
            'CDEF:rx_avg_sum=PREV,UN,0,PREV,IF,rx_avg_sample,+',
            "AREA:tx_avg#$HalfGreen",
            "AREA:rx_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:tx_avg#$FullGreen:Outgoing",
            'GPRINT:tx_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:tx_avg:LAST:%5.1lf%s Last',
            'GPRINT:tx_avg_sum:LAST:(ca. %4.0lf%s Total)\l',
            "LINE1:rx_avg#$FullBlue:Incoming",
            'GPRINT:rx_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:rx_avg:LAST:%5.1lf%s Last',
            'GPRINT:rx_avg_sum:LAST:(ca. %4.0lf%s Total)\l');
    $GraphDefs['if_packets'] = array(
            '-v', 'Packets/s', '--units=si',
            'DEF:tx_avg={file}:tx:AVERAGE',
            'DEF:rx_avg={file}:rx:AVERAGE',
            'CDEF:overlap=tx_avg,rx_avg,GT,rx_avg,tx_avg,IF',
            'CDEF:mytime=tx_avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:tx_avg_sample=tx_avg,UN,0,tx_avg,IF,sample_len,*',
            'CDEF:tx_avg_sum=PREV,UN,0,PREV,IF,tx_avg_sample,+',
            'CDEF:rx_avg_sample=rx_avg,UN,0,rx_avg,IF,sample_len,*',
            'CDEF:rx_avg_sum=PREV,UN,0,PREV,IF,rx_avg_sample,+',
            'CDEF:reverse_tx_avg=0,tx_avg,-',
            "AREA:reverse_tx_avg#$HalfGreen",
            "AREA:rx_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:reverse_tx_avg#$FullGreen:Outgoing",
            'GPRINT:tx_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:tx_avg:LAST:%5.1lf%s Last',
            'GPRINT:tx_avg_sum:LAST:(ca. %4.0lf%s Total)\l',
            "LINE1:rx_avg#$FullBlue:Incoming",
            'GPRINT:rx_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:rx_avg:LAST:%5.1lf%s Last',
            'GPRINT:rx_avg_sum:LAST:(ca. %4.0lf%s Total)\l',
            );
    $GraphDefs['if_rx_errors'] = array(
            '-v', 'Errors/s', '--units=si',
            'DEF:avg={file}:value:AVERAGE',
            'CDEF:mytime=avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:avg_sample=avg,UN,0,avg,IF,sample_len,*',
            'CDEF:avg_sum=PREV,UN,0,PREV,IF,avg_sample,+',
            "AREA:avg#$HalfBlue",
            "LINE1:avg#$FullBlue:Errors/s",
            'GPRINT:avg:AVERAGE:%3.1lf%s Avg,',
            'GPRINT:avg:LAST:%3.1lf%s Last',
            'GPRINT:avg_sum:LAST:(ca. %2.0lf%s Total)\l');
    $GraphDefs['ipt_bytes'] = array(
            '-v', 'Bits/s',
            'DEF:avg_raw={file}:value:AVERAGE',
            'CDEF:avg=avg_raw,8,*',
            'CDEF:mytime=avg_raw,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:avg_sample=avg_raw,UN,0,avg_raw,IF,sample_len,*',
            'CDEF:avg_sum=PREV,UN,0,PREV,IF,avg_sample,+',
            "LINE1:avg#$FullBlue:Bits/s",
            'GPRINT:avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg:LAST:%5.1lf%s Last',
            'GPRINT:avg_sum:LAST:(ca. %5.1lf%sB Total)\l');
    $GraphDefs['ipt_packets'] = array(
            '-v', 'Packets/s',
            'DEF:avg_raw={file}:value:AVERAGE',
            'CDEF:avg=avg_raw,8,*',
            "LINE1:avg#$FullBlue:Packets/s",
            'GPRINT:avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['irq'] = array(
            '-v', 'Issues/s',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Issues/s",
            'GPRINT:avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:avg:LAST:%6.2lf Last\l');
    $GraphDefs['load'] = array(
            '-v', 'System load', '-u', '2',
            'DEF:s_avg={file}:shortterm:AVERAGE',
            'DEF:m_avg={file}:midterm:AVERAGE',
            'DEF:l_avg={file}:longterm:AVERAGE',
            'VDEF:max_s=s_avg,MAXIMUM',
            'VDEF:max_m=m_avg,MAXIMUM',
            'VDEF:max_l=l_avg,MAXIMUM',
            "HRULE:max_s#$FullGreen::dashes",
            "HRULE:max_m#$FullBlue::dashes",
            "HRULE:max_l#$FullRed::dashes",
            "LINE1:s_avg#$FullGreen: 1m",
            'GPRINT:s_avg:LAST:Last\:%4.2lf ',
            'GPRINT:s_avg:AVERAGE:Average\:%4.2lf ',
            'GPRINT:max_s:Max\:%4.2lf \l',
            "LINE1:m_avg#$FullBlue: 5m",
            'GPRINT:m_avg:LAST:Last\:%4.2lf ',
            'GPRINT:m_avg:AVERAGE:Average\:%4.2lf ',
            'GPRINT:max_m:Max\:%4.2lf \l',
            "LINE1:l_avg#$FullRed:15m",
            'GPRINT:l_avg:LAST:Last\:%4.2lf ',
            'GPRINT:l_avg:AVERAGE:Average\:%4.2lf ',
            'GPRINT:max_l:Max\:%4.2lf \l');
    $GraphDefs['load_percent'] = array(
            '-v', '%',
            'DEF:avg={file}:percent:AVERAGE',
            "LINE1:avg#$FullBlue:Load",
            'GPRINT:avg:AVERAGE:%5.1lf%s%% Avg,',
            'GPRINT:avg:LAST:%5.1lf%s%% Last\l');
    $GraphDefs['mails'] = array(
            'DEF:rawgood={file}:good:AVERAGE',
            'DEF:rawspam={file}:spam:AVERAGE',
            'CDEF:good=rawgood,UN,0,rawgood,IF',
            'CDEF:spam=rawspam,UN,0,rawspam,IF',
            'CDEF:negspam=spam,-1,*',
            "AREA:good#$HalfGreen",
            "LINE1:good#$FullGreen:Good mails",
            'GPRINT:good:AVERAGE:%4.1lf Avg,',
            'GPRINT:good:MAX:%4.1lf Max,',
            'GPRINT:good:LAST:%4.1lf Last\n',
            "AREA:negspam#$HalfRed",
            "LINE1:negspam#$FullRed:Spam mails",
            'GPRINT:spam:AVERAGE:%4.1lf Avg,',
            'GPRINT:spam:MAX:%4.1lf Max,',
            'GPRINT:spam:LAST:%4.1lf Last',
            'HRULE:0#000000');
    $GraphDefs['memory'] = array(
            '-b', '1024', '-v', 'Bytes',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Memory",
            'GPRINT:avg:AVERAGE:%5.1lf%sbyte Avg,',
            'GPRINT:avg:LAST:%5.1lf%sbyte Last\l');
    $GraphDefs['old_memory'] = array(
            'DEF:used_avg={file}:used:AVERAGE',
            'DEF:free_avg={file}:free:AVERAGE',
            'DEF:buffers_avg={file}:buffers:AVERAGE',
            'DEF:cached_avg={file}:cached:AVERAGE',
            'CDEF:cached_avg_nn=cached_avg,UN,0,cached_avg,IF',
            'CDEF:buffers_avg_nn=buffers_avg,UN,0,buffers_avg,IF',
            'CDEF:free_cached_buffers_used=free_avg,cached_avg_nn,+,buffers_avg_nn,+,used_avg,+',
            'CDEF:cached_buffers_used=cached_avg,buffers_avg_nn,+,used_avg,+',
            'CDEF:buffers_used=buffers_avg,used_avg,+',
            "AREA:free_cached_buffers_used#$HalfGreen",
            "AREA:cached_buffers_used#$HalfBlue",
            "AREA:buffers_used#$HalfYellow",
            "AREA:used_avg#$HalfRed",
            "LINE1:free_cached_buffers_used#$FullGreen:Free        ",
            'GPRINT:free_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:free_avg:LAST:%5.1lf%s Last\n',
            "LINE1:cached_buffers_used#$FullBlue:Page cache  ",
            'GPRINT:cached_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:cached_avg:LAST:%5.1lf%s Last\n',
            "LINE1:buffers_used#$FullYellow:Buffer cache",
            'GPRINT:buffers_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:buffers_avg:LAST:%5.1lf%s Last\n',
            "LINE1:used_avg#$FullRed:Used        ",
            'GPRINT:used_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:used_avg:LAST:%5.1lf%s Last');
    $GraphDefs['mysql_commands'] = array(
            '-v', 'Issues/s',
            "DEF:val_avg={file}:value:AVERAGE",
            "LINE1:val_avg#$FullBlue:Issues/s",
            'GPRINT:val_avg:AVERAGE:%5.2lf Avg,',
            'GPRINT:val_avg:LAST:%5.2lf Last');
    $GraphDefs['mysql_handler'] = array(
            '-v', 'Issues/s',
            "DEF:val_avg={file}:value:AVERAGE",
            "LINE1:val_avg#$FullBlue:Issues/s",
            'GPRINT:val_avg:AVERAGE:%5.2lf Avg,',
            'GPRINT:val_avg:LAST:%5.2lf Last');
    $GraphDefs['mysql_octets'] = array(
            '-v', 'Bits/s',
            'DEF:out_avg={file}:tx:AVERAGE',
            'DEF:inc_avg={file}:rx:AVERAGE',
            'CDEF:mytime=out_avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:out_avg_sample=out_avg,UN,0,out_avg,IF,sample_len,*',
            'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
            'CDEF:inc_avg_sample=inc_avg,UN,0,inc_avg,IF,sample_len,*',
            'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
            'CDEF:out_bit_avg=out_avg,8,*',
            'CDEF:inc_bit_avg=inc_avg,8,*',
            'CDEF:overlap=out_bit_avg,inc_bit_avg,GT,inc_bit_avg,out_bit_avg,IF',
            "AREA:out_bit_avg#$HalfGreen",
            "AREA:inc_bit_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:out_bit_avg#$FullGreen:Written",
            'GPRINT:out_bit_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:out_bit_avg:LAST:%5.1lf%s Last',
            'GPRINT:out_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
            "LINE1:inc_bit_avg#$FullBlue:Read   ",
            'GPRINT:inc_bit_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:inc_bit_avg:LAST:%5.1lf%s Last',
            'GPRINT:inc_avg_sum:LAST:(ca. %5.1lf%sB Total)\l');
    $GraphDefs['mysql_qcache'] = array(
            '-v', 'Queries/s',
            "DEF:hits_avg={file}:hits:AVERAGE",
            "DEF:inserts_avg={file}:inserts:AVERAGE",
            "DEF:not_cached_avg={file}:not_cached:AVERAGE",
            "DEF:lowmem_prunes_avg={file}:lowmem_prunes:AVERAGE",
            "DEF:queries_avg={file}:queries_in_cache:AVERAGE",
            "CDEF:unknown=queries_avg,UNKN,+",
            "CDEF:not_cached_agg=hits_avg,inserts_avg,+,not_cached_avg,+",
            "CDEF:inserts_agg=hits_avg,inserts_avg,+",
            "CDEF:hits_agg=hits_avg",
            "AREA:not_cached_agg#$HalfYellow",
            "AREA:inserts_agg#$HalfBlue",
            "AREA:hits_agg#$HalfGreen",
            "LINE1:not_cached_agg#$FullYellow:Not Cached      ",
            'GPRINT:not_cached_avg:AVERAGE:%5.2lf Avg,',
            'GPRINT:not_cached_avg:LAST:%5.2lf Last\l',
            "LINE1:inserts_agg#$FullBlue:Inserts         ",
            'GPRINT:inserts_avg:AVERAGE:%5.2lf Avg,',
            'GPRINT:inserts_avg:LAST:%5.2lf Last\l',
            "LINE1:hits_agg#$FullGreen:Hits            ",
            'GPRINT:hits_avg:AVERAGE:%5.2lf Avg,',
            'GPRINT:hits_avg:LAST:%5.2lf Last\l',
            "LINE1:lowmem_prunes_avg#$FullRed:Lowmem Prunes   ",
            'GPRINT:lowmem_prunes_avg:AVERAGE:%5.2lf Avg,',
            'GPRINT:lowmem_prunes_avg:LAST:%5.2lf Last\l',
            "LINE1:unknown#$Canvas:Queries in cache",
            'GPRINT:queries_avg:AVERAGE:%5.0lf Avg,',
            'GPRINT:queries_avg:LAST:%5.0lf Last\l');
    $GraphDefs['mysql_threads'] = array(
            '-v', 'Threads',
            "DEF:running_avg={file}:running:AVERAGE",
            "DEF:connected_avg={file}:connected:AVERAGE",
            "DEF:cached_avg={file}:cached:AVERAGE",
            "DEF:created_avg={file}:created:AVERAGE",
            "CDEF:unknown=created_avg,UNKN,+",
            "CDEF:cached_agg=connected_avg,cached_avg,+",
            "AREA:cached_agg#$HalfGreen",
            "AREA:connected_avg#$HalfBlue",
            "AREA:running_avg#$HalfRed",
            "LINE1:cached_agg#$FullGreen:Cached   ",
            'GPRINT:cached_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:cached_avg:LAST:%5.1lf Last\l',
            "LINE1:connected_avg#$FullBlue:Connected",
            'GPRINT:connected_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:connected_avg:LAST:%5.1lf Last\l',
            "LINE1:running_avg#$FullRed:Running  ",
            'GPRINT:running_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:running_avg:LAST:%5.1lf Last\l',
            "LINE1:unknown#$Canvas:Created  ",
            'GPRINT:created_avg:AVERAGE:%5.0lf Avg,',
            'GPRINT:created_avg:LAST:%5.0lf Last\l');
    $GraphDefs['panfs_procedure_ok'] = array(
            '-v', 'Issues/s',
            'DEF:open_avg={file}:open:AVERAGE',
            'DEF:close_avg={file}:close:AVERAGE',
            'DEF:read_avg={file}:read:AVERAGE',
            'DEF:write_avg={file}:write:AVERAGE',
            'DEF:getattr_avg={file}:getattr:AVERAGE',
            'DEF:setattr_avg={file}:setattr:AVERAGE',
            'DEF:lookup_avg={file}:lookup:AVERAGE',
            'DEF:permission_avg={file}:permission:AVERAGE',
            'LINE1:open_avg#FF0000:open       ',
            'GPRINT:open_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:open_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:close_avg#00E000:close      ',
            'GPRINT:close_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:close_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:read_avg#0000FF:read       ',
            'GPRINT:read_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:read_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:write_avg#F0A000:write      ',
            'GPRINT:write_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:write_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:getattr_avg#00A0FF:getattr    ',
            'GPRINT:getattr_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:getattr_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:setattr_avg#A000FF:setattr    ',
            'GPRINT:setattr_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:setattr_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:lookup_avg#FF8C00:lookup     ',
            'GPRINT:lookup_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:lookup_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:permission_avg#AAFF00:permission ',
            'GPRINT:permission_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:permission_avg:LAST:Last\:%5.1lf%s\l'
                );
    $GraphDefs['panfs_procedure_ok_time'] = array(
            '-v', 'Seconds',
            'DEF:open_avg={file}:open:AVERAGE',
            'DEF:close_avg={file}:close:AVERAGE',
            'DEF:read_avg={file}:read:AVERAGE',
            'DEF:write_avg={file}:write:AVERAGE',
            'DEF:getattr_avg={file}:getattr:AVERAGE',
            'DEF:setattr_avg={file}:setattr:AVERAGE',
            'DEF:lookup_avg={file}:lookup:AVERAGE',
            'DEF:permission_avg={file}:permission:AVERAGE',
            'LINE1:open_avg#FF0000:open       ',
            'GPRINT:open_avg:AVERAGE:Average\:%5.1lf%ss  ',
            'GPRINT:open_avg:LAST:Last\:%5.1lf%ss\l',
            'LINE1:close_avg#00E000:close      ',
            'GPRINT:close_avg:AVERAGE:Average\:%5.1lf%ss  ',
            'GPRINT:close_avg:LAST:Last\:%5.1lf%ss\l',
            'LINE1:read_avg#0000FF:read       ',
            'GPRINT:read_avg:AVERAGE:Average\:%5.1lf%ss  ',
            'GPRINT:read_avg:LAST:Last\:%5.1lf%ss\l',
            'LINE1:write_avg#F0A000:write      ',
            'GPRINT:write_avg:AVERAGE:Average\:%5.1lf%ss  ',
            'GPRINT:write_avg:LAST:Last\:%5.1lf%ss\l',
            'LINE1:getattr_avg#00A0FF:getattr    ',
            'GPRINT:getattr_avg:AVERAGE:Average\:%5.1lf%ss  ',
            'GPRINT:getattr_avg:LAST:Last\:%5.1lf%ss\l',
            'LINE1:setattr_avg#A000FF:setattr    ',
            'GPRINT:setattr_avg:AVERAGE:Average\:%5.1lf%ss  ',
            'GPRINT:setattr_avg:LAST:Last\:%5.1lf%ss\l',
            'LINE1:lookup_avg#FF8C00:lookup     ',
            'GPRINT:lookup_avg:AVERAGE:Average\:%5.1lf%ss  ',
            'GPRINT:lookup_avg:LAST:Last\:%5.1lf%ss\l',
            'LINE1:permission_avg#AAFF00:permission ',
            'GPRINT:permission_avg:AVERAGE:Average\:%5.1lf%ss  ',
            'GPRINT:permission_avg:LAST:Last\:%5.1lf%ss\l'
                );
    $GraphDefs['panfs_procedure_ko'] = array(
            '-v', 'Issues/s',
            'DEF:open_avg={file}:open:AVERAGE',
            'DEF:close_avg={file}:close:AVERAGE',
            'DEF:read_avg={file}:read:AVERAGE',
            'DEF:write_avg={file}:write:AVERAGE',
            'DEF:getattr_avg={file}:getattr:AVERAGE',
            'DEF:setattr_avg={file}:setattr:AVERAGE',
            'DEF:lookup_avg={file}:lookup:AVERAGE',
            'DEF:permission_avg={file}:permission:AVERAGE',
            'LINE1:open_avg#FF0000:open       ',
            'GPRINT:open_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:open_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:close_avg#00E000:close      ',
            'GPRINT:close_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:close_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:read_avg#0000FF:read       ',
            'GPRINT:read_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:read_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:write_avg#F0A000:write      ',
            'GPRINT:write_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:write_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:getattr_avg#00A0FF:getattr    ',
            'GPRINT:getattr_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:getattr_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:setattr_avg#A000FF:setattr    ',
            'GPRINT:setattr_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:setattr_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:lookup_avg#FF8C00:lookup     ',
            'GPRINT:lookup_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:lookup_avg:LAST:Last\:%5.1lf%s\l',
            'LINE1:permission_avg#AAFF00:permission ',
            'GPRINT:permission_avg:AVERAGE:Average\:%5.1lf%s  ',
            'GPRINT:permission_avg:LAST:Last\:%5.1lf%s\l'
                );
    $GraphDefs['pressure'] = array(
            '-v', 'millibar', '-X', '-1', '-b', '1000',
            '--units-exponent', '0', '-Y',
            '-l', '860', '-u', '1090', '-r',
            'DEF:value={file}:value:AVERAGE',
            //'CDEF:value=value_avg,1000,/',
            'LINE1:value#FF0000:value ',
            'GPRINT:value:AVERAGE:Average\:%4.0lf millibar  ',
            'GPRINT:value:LAST:Last\:%4.0lf millibar\l'
            );
    $GraphDefs['nfs_procedure'] = array(
            '-v', 'Issues/s',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Issues/s",
            'GPRINT:avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:avg:LAST:%6.2lf Last\l');
    $GraphDefs['nfs3_procedures'] = array(
            "DEF:null_avg={file}:null:AVERAGE",
            "DEF:getattr_avg={file}:getattr:AVERAGE",
            "DEF:setattr_avg={file}:setattr:AVERAGE",
            "DEF:lookup_avg={file}:lookup:AVERAGE",
            "DEF:access_avg={file}:access:AVERAGE",
            "DEF:readlink_avg={file}:readlink:AVERAGE",
            "DEF:read_avg={file}:read:AVERAGE",
            "DEF:write_avg={file}:write:AVERAGE",
            "DEF:create_avg={file}:create:AVERAGE",
            "DEF:mkdir_avg={file}:mkdir:AVERAGE",
            "DEF:symlink_avg={file}:symlink:AVERAGE",
            "DEF:mknod_avg={file}:mknod:AVERAGE",
            "DEF:remove_avg={file}:remove:AVERAGE",
            "DEF:rmdir_avg={file}:rmdir:AVERAGE",
            "DEF:rename_avg={file}:rename:AVERAGE",
            "DEF:link_avg={file}:link:AVERAGE",
            "DEF:readdir_avg={file}:readdir:AVERAGE",
            "DEF:readdirplus_avg={file}:readdirplus:AVERAGE",
            "DEF:fsstat_avg={file}:fsstat:AVERAGE",
            "DEF:fsinfo_avg={file}:fsinfo:AVERAGE",
            "DEF:pathconf_avg={file}:pathconf:AVERAGE",
            "DEF:commit_avg={file}:commit:AVERAGE",
            "CDEF:other_avg=null_avg,readlink_avg,create_avg,mkdir_avg,symlink_avg,mknod_avg,remove_avg,rmdir_avg,rename_avg,link_avg,readdir_avg,readdirplus_avg,fsstat_avg,fsinfo_avg,pathconf_avg,+,+,+,+,+,+,+,+,+,+,+,+,+,+",
            "CDEF:stack_read=read_avg",
            "CDEF:stack_getattr=stack_read,getattr_avg,+",
            "CDEF:stack_access=stack_getattr,access_avg,+",
            "CDEF:stack_lookup=stack_access,lookup_avg,+",
            "CDEF:stack_write=stack_lookup,write_avg,+",
            "CDEF:stack_commit=stack_write,commit_avg,+",
            "CDEF:stack_setattr=stack_commit,setattr_avg,+",
            "CDEF:stack_other=stack_setattr,other_avg,+",
            "AREA:stack_other#$HalfRed",
            "AREA:stack_setattr#$HalfGreen",
            "AREA:stack_commit#$HalfYellow",
            "AREA:stack_write#$HalfGreen",
            "AREA:stack_lookup#$HalfBlue",
            "AREA:stack_access#$HalfMagenta",
            "AREA:stack_getattr#$HalfCyan",
            "AREA:stack_read#$HalfBlue",
            "LINE1:stack_other#$FullRed:Other  ",
            'GPRINT:other_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:other_avg:LAST:%5.1lf Last\l',
            "LINE1:stack_setattr#$FullGreen:setattr",
            'GPRINT:setattr_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:setattr_avg:LAST:%5.1lf Last\l',
            "LINE1:stack_commit#$FullYellow:commit ",
            'GPRINT:commit_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:commit_avg:LAST:%5.1lf Last\l',
            "LINE1:stack_write#$FullGreen:write  ",
            'GPRINT:write_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:write_avg:LAST:%5.1lf Last\l',
            "LINE1:stack_lookup#$FullBlue:lookup ",
            'GPRINT:lookup_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:lookup_avg:LAST:%5.1lf Last\l',
            "LINE1:stack_access#$FullMagenta:access ",
            'GPRINT:access_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:access_avg:LAST:%5.1lf Last\l',
            "LINE1:stack_getattr#$FullCyan:getattr",
            'GPRINT:getattr_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:getattr_avg:LAST:%5.1lf Last\l',
            "LINE1:stack_read#$FullBlue:read   ",
            'GPRINT:read_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:read_avg:LAST:%5.1lf Last\l');
    $GraphDefs['opcode'] = array(
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Queries/s",
            'GPRINT:avg:AVERAGE:%9.3lf Average,',
            'GPRINT:avg:LAST:%9.3lf Last\l');
    $GraphDefs['partition'] = array(
            "DEF:rbyte_avg={file}:rbytes:AVERAGE",
            "DEF:wbyte_avg={file}:wbytes:AVERAGE",
            'CDEF:overlap=wbyte_avg,rbyte_avg,GT,rbyte_avg,wbyte_avg,IF',
            "AREA:wbyte_avg#$HalfGreen",
            "AREA:rbyte_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",    "LINE1:wbyte_avg#$FullGreen:Write",
            'GPRINT:wbyte_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:wbyte_avg:LAST:%5.1lf%s Last\l',
            "LINE1:rbyte_avg#$FullBlue:Read ",
            'GPRINT:rbyte_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:rbyte_avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['percent'] = array(
            '-v', 'Percent', '-r', '-l', '0', '-u', '100',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Percent",
            'GPRINT:avg:AVERAGE:%5.1lf%% Avg,',
            'GPRINT:avg:LAST:%5.1lf%% Last\l');
    $GraphDefs['ping'] = array(
            'DEF:ping_avg={file}:ping:AVERAGE',
            "LINE1:ping_avg#$FullBlue:Ping",
            'GPRINT:ping_avg:AVERAGE:%4.1lf ms Avg,',
            'GPRINT:ping_avg:LAST:%4.1lf ms Last');
    $GraphDefs['power'] = $GraphDefs['current'];
    /*
       $GraphDefs['power'] = array(
       '-v', 'Watt',
       'DEF:avg={file}:value:AVERAGE',
       'VDEF:max=avg,MAXIMUM',
       "LINE1:avg#$FullBlue:Watt",
       'GPRINT:avg:AVERAGE:%5.1lf%sW Avg,',
       'GPRINT:avg:LAST:%5.1lf%sW Last\n',
       "HRULE:max#$FullRed:Max:dashes",
       'GPRINT:max:%5.1lf%sW\l'
       );
     */
    $GraphDefs['processes'] = array(
            "DEF:running_avg={file}:running:AVERAGE",
            "DEF:sleeping_avg={file}:sleeping:AVERAGE",
            "DEF:zombies_avg={file}:zombies:AVERAGE",
            "DEF:stopped_avg={file}:stopped:AVERAGE",
            "DEF:paging_avg={file}:paging:AVERAGE",
            "DEF:blocked_avg={file}:blocked:AVERAGE",
            'CDEF:paging_acc=sleeping_avg,running_avg,stopped_avg,zombies_avg,blocked_avg,paging_avg,+,+,+,+,+',
            'CDEF:blocked_acc=sleeping_avg,running_avg,stopped_avg,zombies_avg,blocked_avg,+,+,+,+',
            'CDEF:zombies_acc=sleeping_avg,running_avg,stopped_avg,zombies_avg,+,+,+',
            'CDEF:stopped_acc=sleeping_avg,running_avg,stopped_avg,+,+',
            'CDEF:running_acc=sleeping_avg,running_avg,+',
            'CDEF:sleeping_acc=sleeping_avg',
            "AREA:paging_acc#$HalfYellow",
            "AREA:blocked_acc#$HalfCyan",
            "AREA:zombies_acc#$HalfRed",
            "AREA:stopped_acc#$HalfMagenta",
            "AREA:running_acc#$HalfGreen",
            "AREA:sleeping_acc#$HalfBlue",
            "LINE1:paging_acc#$FullYellow:Paging  ",
            'GPRINT:paging_avg:AVERAGE:%5.1lf Average,',
            'GPRINT:paging_avg:LAST:%5.1lf Last\l',
            "LINE1:blocked_acc#$FullCyan:Blocked ",
            'GPRINT:blocked_avg:AVERAGE:%5.1lf Average,',
            'GPRINT:blocked_avg:LAST:%5.1lf Last\l',
            "LINE1:zombies_acc#$FullRed:Zombies ",
            'GPRINT:zombies_avg:AVERAGE:%5.1lf Average,',
            'GPRINT:zombies_avg:LAST:%5.1lf Last\l',
            "LINE1:stopped_acc#$FullMagenta:Stopped ",
            'GPRINT:stopped_avg:AVERAGE:%5.1lf Average,',
            'GPRINT:stopped_avg:LAST:%5.1lf Last\l',
            "LINE1:running_acc#$FullGreen:Running ",
            'GPRINT:running_avg:AVERAGE:%5.1lf Average,',
            'GPRINT:running_avg:LAST:%5.1lf Last\l',
            "LINE1:sleeping_acc#$FullBlue:Sleeping",
            'GPRINT:sleeping_avg:AVERAGE:%5.1lf Average,',
            'GPRINT:sleeping_avg:LAST:%5.1lf Last\l');
    $GraphDefs['ps_count'] = array(
            '-v', 'Processes',
            'DEF:procs_avg={file}:processes:AVERAGE',
            'DEF:thrds_avg={file}:threads:AVERAGE',
            "AREA:thrds_avg#$HalfBlue",
            "AREA:procs_avg#$HalfRed",
            "LINE1:thrds_avg#$FullBlue:Threads  ",
            'GPRINT:thrds_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:thrds_avg:LAST:%5.1lf Last\l',
            "LINE1:procs_avg#$FullRed:Processes",
            'GPRINT:procs_avg:AVERAGE:%5.1lf Avg,',
            'GPRINT:procs_avg:LAST:%5.1lf Last\l');
    $GraphDefs['ps_cputime'] = array(
            '-v', 'Jiffies',
            'DEF:user_avg_raw={file}:user:AVERAGE',
            'DEF:syst_avg_raw={file}:syst:AVERAGE',
            'CDEF:user_avg=user_avg_raw,1000000,/',
            'CDEF:syst_avg=syst_avg_raw,1000000,/',
            'CDEF:user_syst=syst_avg,UN,0,syst_avg,IF,user_avg,+',
            "AREA:user_syst#$HalfBlue",
            "AREA:syst_avg#$HalfRed",
            "LINE1:user_syst#$FullBlue:User  ",
            'GPRINT:user_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:user_avg:LAST:%5.1lf%s Last\l',
            "LINE1:syst_avg#$FullRed:System",
            'GPRINT:syst_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:syst_avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['ps_disk_ops'] = array(
            '-v Ops/s',
            'DEF:read_avg={file}:read:AVERAGE',
            'DEF:write_avg={file}:write:AVERAGE',
            'CDEF:reverse_write_avg=0,write_avg,-',
            "AREA:reverse_write_avg#$HalfRed",
            "AREA:write_avg#$HalfGreen",
            'LINE1:read_avg#FF0000:read  ',
            'GPRINT:read_avg:LAST:Last\:%5.1lf%s Ops/s',
            'GPRINT:read_avg:AVERAGE:Average\:%5.1lf%s Ops/s\n',
            'LINE1:reverse_write_avg#00E000:write ',
            'GPRINT:write_avg:LAST:Last\:%5.1lf%s Ops/s',
            'GPRINT:write_avg:AVERAGE:Average\:%5.1lf%s Ops/s\n',
            );
    $GraphDefs['ps_pagefaults'] = array(
            '-v', 'Pagefaults/s',
            'DEF:major_avg={file}:majflt:AVERAGE',
            "AREA:major_avg#$HalfRed",
            "LINE1:major_avg#$FullRed:Major",
            'GPRINT:major_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:major_avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['ps_rss'] = array(
            '-v', 'Bytes',
            'DEF:avg={file}:value:AVERAGE',
            "AREA:avg#$HalfBlue",
            "LINE1:avg#$FullBlue:RSS",
            'GPRINT:avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['ps_state'] = array(
            '-v', 'Processes',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Processes",
            'GPRINT:avg:AVERAGE:%6.2lf Avg,',
            'GPRINT:avg:LAST:%6.2lf Last\l');
    $GraphDefs['qtype'] = array(
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Queries/s",
            'GPRINT:avg:AVERAGE:%9.3lf Average,',
            'GPRINT:avg:LAST:%9.3lf Last\l');
    $GraphDefs['rcode'] = array(
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Queries/s",
            'GPRINT:avg:AVERAGE:%9.3lf Average,',
            'GPRINT:avg:LAST:%9.3lf Last\l');
    $GraphDefs['response_time'] = array(
            '-v', 'Seconde(s)',
            'DEF:mavg={file}:value:AVERAGE',
            'CDEF:avg=mavg,1000,/',
            "LINE1:avg#$FullBlue:Response Time",
            'GPRINT:avg:AVERAGE:%9.3lf %ss Average,',
            'GPRINT:avg:LAST:%9.3lf %ss Last\l');
    $GraphDefs['swap'] = array(
            '-v', 'Bytes', '-b', '1024',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Bytes",
            'GPRINT:avg:AVERAGE:%6.2lf%sByte Avg,',
            'GPRINT:avg:LAST:%6.2lf%sByte Last\l');
    $GraphDefs['old_swap'] = array(
            'DEF:used_avg={file}:used:AVERAGE',
            'DEF:free_avg={file}:free:AVERAGE',
            'DEF:cach_avg={file}:cached:AVERAGE',
            'DEF:resv_avg={file}:resv:AVERAGE',
            'CDEF:cach_avg_notnull=cach_avg,UN,0,cach_avg,IF',
            'CDEF:resv_avg_notnull=resv_avg,UN,0,resv_avg,IF',
            'CDEF:used_acc=used_avg',
            'CDEF:resv_acc=used_acc,resv_avg_notnull,+',
            'CDEF:cach_acc=resv_acc,cach_avg_notnull,+',
            'CDEF:free_acc=cach_acc,free_avg,+',
            "AREA:free_acc#$HalfGreen",
            "AREA:cach_acc#$HalfBlue",
            "AREA:resv_acc#$HalfYellow",
            "AREA:used_acc#$HalfRed",
            "LINE1:free_acc#$FullGreen:Free    ",
            'GPRINT:free_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:free_avg:LAST:%5.1lf%s Last\n',
            "LINE1:cach_acc#$FullBlue:Cached  ",
            'GPRINT:cach_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:cach_avg:LAST:%5.1lf%s Last\l',
            "LINE1:resv_acc#$FullYellow:Reserved",
            'GPRINT:resv_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:resv_avg:LAST:%5.1lf%s Last\n',
            "LINE1:used_acc#$FullRed:Used    ",
            'GPRINT:used_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:used_avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['tcp_connections'] = array(
            '-v', 'Connections',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Connections",
            'GPRINT:avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:avg:LAST:%4.1lf Last\l');
    $GraphDefs['temperature'] = array(
            '-v', 'Celsius',
            'DEF:temp_avg={file}:value:AVERAGE',
            'CDEF:average=temp_avg,0.2,*,PREV,UN,temp_avg,PREV,IF,0.8,*,+',
            "LINE1:temp_avg#$FullRed:Temperature",
            'GPRINT:temp_avg:AVERAGE:%4.1lf Avg,',
            'GPRINT:temp_avg:LAST:%4.1lf Last\l');
    $GraphDefs['timeleft'] = array(
            '-v', 'Secondes',
            'DEF:avg={file}:value:AVERAGE',
            'GPRINT:avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['time_offset'] = array( # NTPd
            'DEF:s_avg={file}:value:AVERAGE',
            "LINE1:s_avg#$FullBlue:{inst}",
            'GPRINT:s_avg:AVERAGE:%7.3lf%s Avg,',
            'GPRINT:s_avg:LAST:%7.3lf%s Last');
    $GraphDefs['if_octets'] = array(
            '-v', 'Bytes/s', '--units=si',
            'DEF:out_avg={file}:tx:AVERAGE',
            'DEF:inc_avg={file}:rx:AVERAGE',
            'VDEF:out_max=out_avg,MAXIMUM',
            'VDEF:inc_max=inc_avg,MAXIMUM',
            'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
            'CDEF:mytime=out_avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:out_avg_sample=out_avg,UN,0,out_avg,IF,sample_len,*',
            'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
            'CDEF:inc_avg_sample=inc_avg,UN,0,inc_avg,IF,sample_len,*',
            'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
            'CDEF:reverse_out_avg=0,out_avg,-',
            'VDEF:out_avg95pct=out_avg,95,PERCENTNAN',
            'VDEF:inc_avg95pct=inc_avg,95,PERCENTNAN',
            'VDEF:reverse_out_avg95pct=reverse_out_avg,5,PERCENTNAN',
            "AREA:reverse_out_avg#$HalfGreen",
            "AREA:inc_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:reverse_out_avg#$FullGreen:Outgoing",
            'GPRINT:out_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:out_avg:LAST:%5.1lf%s Last',
            'GPRINT:out_max:%5.1lf%s Max',
            'GPRINT:out_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
            "LINE1:inc_avg#$FullBlue:Incoming",
            'GPRINT:inc_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:inc_avg:LAST:%5.1lf%s Last',
            'GPRINT:inc_max:%5.1lf%s Max',
            'GPRINT:inc_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
            "HRULE:reverse_out_avg95pct#$FullGreen:95 percentile outgoing:dashes",
            'GPRINT:out_avg95pct:%5.1lf%s\l',
            "HRULE:inc_avg95pct#$FullBlue:95 percentile incoming:dashes",
            'GPRINT:inc_avg95pct:%5.1lf%s\l'
                );
    $GraphDefs['throughputbit'] = array(
            '-v', 'Bytes/s', '--units=si',
            'DEF:out_avg={file}:write:AVERAGE',
            'DEF:inc_avg={file}:read:AVERAGE',
            'CDEF:reverse_out_avg=0,out_avg,-',
            'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
            'CDEF:mytime=out_avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:out_avg_sample=out_avg,UN,0,out_avg,IF,sample_len,*',
            'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
            'CDEF:inc_avg_sample=inc_avg,UN,0,inc_avg,IF,sample_len,*',
            'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
            "AREA:reverse_out_avg#$HalfGreen",
            "AREA:inc_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:reverse_out_avg#$FullGreen:Written",
            'GPRINT:out_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:out_avg:LAST:%5.1lf%s Last',
            'GPRINT:out_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
            "LINE1:inc_avg#$FullBlue:Read   ",
            'GPRINT:inc_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:inc_avg:LAST:%5.1lf%s Last',
            'GPRINT:inc_avg_sum:LAST:(ca. %5.1lf%sB Total)\l');
    $GraphDefs['cpufreq'] = array(
            'DEF:cpufreq_avg={file}:value:AVERAGE',
            "LINE1:cpufreq_avg#$FullBlue:Frequency",
            'GPRINT:cpufreq_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:cpufreq_avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['multimeter'] = array(
            'DEF:multimeter_avg={file}:value:AVERAGE',
            "LINE1:multimeter_avg#$FullBlue:Multimeter",
            'GPRINT:multimeter_avg:AVERAGE:%4.1lf Average,',
            'GPRINT:multimeter_avg:LAST:%4.1lf Last\l');
    $GraphDefs['uptime'] = array(
            '-v', 'Days',
            'DEF:avg={file}:value:AVERAGE',
            'CDEF:avgd=avg,86400,/',
            'VDEF:maxd=avgd,MAXIMUM',
            'VDEF:avgd2=avgd,AVERAGE',
            'CDEF:down=avg,UN,INF,0,IF',
            "AREA:avgd#D8D8D8",
            "LINE1:avgd#F17742:Last\:",
            'GPRINT:avgd:LAST:%5.0lf day(s)\l',
            "HRULE:maxd#DA1F3D:Maximum\::dashes",
            'GPRINT:maxd:%5.0lf day(s)\l',
            "HRULE:avgd2#6CABE7:Average\::dashes",
            'GPRINT:avgd2:%5.0lf day(s)\l',
            "AREA:down#$HalfRed:Server down\l"
            );
    /*	$GraphDefs['users'] = array(
        '-v', 'Users',
        'DEF:users_avg={file}:value:AVERAGE',
        "LINE1:users_avg#$FullBlue:Users",
        'GPRINT:users_avg:AVERAGE:%4.1lf Average,',
        'GPRINT:users_avg:LAST:%4.1lf Last\l');
     */
    $GraphDefs['voltage'] = array(
            '-v', 'Voltage',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Voltage",
            'GPRINT:avg:AVERAGE:%5.1lf%sV Avg,',
            'GPRINT:avg:LAST:%5.1lf%sV Last\l');
    $GraphDefs['vmpage_action'] = array(
            '-v', 'Actions',
            'DEF:avg={file}:value:AVERAGE',
            "LINE1:avg#$FullBlue:Action",
            'GPRINT:avg:AVERAGE:%5.1lf%sV Avg,',
            'GPRINT:avg:LAST:%5.1lf%sV Last\l');
    $GraphDefs['vmpage_faults'] = $GraphDefs['ps_pagefaults'];
    $GraphDefs['vmpage_io'] = array(
            '-v', 'Pages/s',
            'DEF:out_avg={file}:out:AVERAGE',
            'DEF:inc_avg={file}:in:AVERAGE',
            'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
            'CDEF:mytime=out_avg,TIME,TIME,IF',
            'CDEF:sample_len_raw=mytime,PREV(mytime),-',
            'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
            'CDEF:out_avg_sample=out_avg,UN,0,out_avg,IF,sample_len,*',
            'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
            'CDEF:inc_avg_sample=inc_avg,UN,0,inc_avg,IF,sample_len,*',
            'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
            "AREA:out_avg#$HalfGreen",
            "AREA:inc_avg#$HalfBlue",
            "AREA:overlap#$HalfBlueGreen",
            "LINE1:out_avg#$FullGreen:Out",
            'GPRINT:out_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:out_avg:LAST:%5.1lf%s Last\l',
            "LINE1:inc_avg#$FullBlue:In ",
            'GPRINT:inc_avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:inc_avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['vmpage_number'] = array(
            '-v', 'Count',
            'DEF:avg={file}:value:AVERAGE',
            "AREA:avg#$HalfBlue",
            "LINE1:avg#$FullBlue:Count",
            'GPRINT:avg:AVERAGE:%5.1lf%s Avg,',
            'GPRINT:avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['vs_threads'] = array(
            "DEF:total_avg={file}:total:AVERAGE",
            "DEF:running_avg={file}:running:AVERAGE",
            "DEF:uninterruptible_avg={file}:uninterruptible:AVERAGE",
            "DEF:onhold_avg={file}:onhold:AVERAGE",
            "LINE1:total_avg#$FullYellow:Total   ",
            'GPRINT:total_avg:AVERAGE:%5.1lf Avg.,',
            'GPRINT:total_avg:LAST:%5.1lf Last\l',
            "LINE1:running_avg#$FullRed:Running ",
            'GPRINT:running_avg:AVERAGE:%5.1lf Avg.,',
            'GPRINT:running_avg:LAST:%5.1lf Last\l',
            "LINE1:uninterruptible_avg#$FullGreen:Unintr  ",
            'GPRINT:uninterruptible_avg:AVERAGE:%5.1lf Avg.,',
            'GPRINT:uninterruptible_avg:LAST:%5.1lf Last\l',
            "LINE1:onhold_avg#$FullBlue:Onhold  ",
            'GPRINT:onhold_avg:AVERAGE:%5.1lf Avg.,',
            'GPRINT:onhold_avg:LAST:%5.1lf Last\l');
    $GraphDefs['vs_memory'] = array(
            'DEF:vm_avg={file}:vm:AVERAGE',
            'DEF:vml_avg={file}:vml:AVERAGE',
            'DEF:rss_avg={file}:rss:AVERAGE',
            'DEF:anon_avg={file}:anon:AVERAGE',
            "LINE1:vm_avg#$FullYellow:VM     ",
            'GPRINT:vm_avg:AVERAGE:%5.1lf%s Avg.,',
            'GPRINT:vm_avg:LAST:%5.1lf%s Last\l',
            "LINE1:vml_avg#$FullRed:Locked ",
            'GPRINT:vml_avg:AVERAGE:%5.1lf%s Avg.,',
            'GPRINT:vml_avg:LAST:%5.1lf%s Last\l',
            "LINE1:rss_avg#$FullGreen:RSS    ",
            'GPRINT:rss_avg:AVERAGE:%5.1lf%s Avg.,',
            'GPRINT:rss_avg:LAST:%5.1lf%s Last\l',
            "LINE1:anon_avg#$FullBlue:Anon.  ",
            'GPRINT:anon_avg:AVERAGE:%5.1lf%s Avg.,',
            'GPRINT:anon_avg:LAST:%5.1lf%s Last\l');
    $GraphDefs['vs_processes'] = array(
            '-v', 'Processes',
            'DEF:proc_avg={file}:value:AVERAGE',
            "LINE1:proc_avg#$FullBlue:Processes",
            'GPRINT:proc_avg:AVERAGE:%4.1lf Avg.,',
            'GPRINT:proc_avg:LAST:%4.1lf Last\l');
    $GraphDefs['if_multicast'] = $GraphDefs['ipt_packets'];
    $GraphDefs['if_tx_errors'] = $GraphDefs['if_rx_errors'];

    $MetaGraphDefs['nb_values']         = 'meta_graph_nb_values';
    $MetaGraphDefs['files_count']       = 'meta_graph_files_count';
    $MetaGraphDefs['files_size']        = 'meta_graph_files_size';
    $MetaGraphDefs['users']             = 'meta_graph_users';
    $MetaGraphDefs['cache_entries'] 	= 'meta_graph_cache_entries';
    $MetaGraphDefs['celerra_if_errors'] = 'meta_graph_celerra_if';
    $MetaGraphDefs['celerra_if_octets'] = 'meta_graph_celerra_if';
    $MetaGraphDefs['celerra_if_packets'] = 'meta_graph_celerra_if';
    $MetaGraphDefs['celerra_io'] 		= 'meta_graph_celerra_io';
    $MetaGraphDefs['celerra_octets']	= 'meta_graph_celerra_io';
    $MetaGraphDefs['celerra_packetsize'] = 'meta_graph_celerra_io';
    $MetaGraphDefs['celerra_percent'] = 'meta_graph_celerra_io';
    $MetaGraphDefs['cpu']               = 'meta_graph_cpu';
    $MetaGraphDefs['cpug']              = 'meta_graph_cpu';
    $MetaGraphDefs['cpufreq']           = 'meta_graph_cpufreq';
    $MetaGraphDefs['grid']              = 'meta_graph_grid';
    $MetaGraphDefs['specs']             = 'meta_graph_specs';
    $MetaGraphDefs['swap_io']           = 'meta_graph_swap_io';
    //	$MetaGraphDefs['df_complex']        = 'meta_graph_df_complex';
    $MetaGraphDefs['if_rx_errors']      = 'meta_graph_if_rx_errors';
    $MetaGraphDefs['if_tx_errors']      = 'meta_graph_if_rx_errors';
    $MetaGraphDefs['irq']               = 'meta_graph_irq';
    $MetaGraphDefs['memory']            = 'meta_graph_memory';
    $MetaGraphDefs['vs_memory']         = 'meta_graph_vs_memory';
    $MetaGraphDefs['threads']        	= 'meta_graph_mysql_threads';
    $MetaGraphDefs['vs_threads']        = 'meta_graph_vs_threads';
    $MetaGraphDefs['nfs_procedure']     = 'meta_graph_nfs_procedure';
    $MetaGraphDefs['ps_state']          = 'meta_graph_ps_state';
    $MetaGraphDefs['swap']              = 'meta_graph_swap';
    $MetaGraphDefs['apache_scoreboard'] = 'meta_graph_apache_scoreboard';
    $MetaGraphDefs['mysql_commands']    = 'meta_graph_mysql_commands';
    $MetaGraphDefs['mysql_handler']     = 'meta_graph_mysql_commands';
    $MetaGraphDefs['tcp_connections']   = 'meta_graph_tcp_connections';
    $MetaGraphDefs['dns_opcode']        = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_qtype']         = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_qtype_cached']  = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_rcode']         = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_request']       = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_resolver']      = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_update']        = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_zops']          = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_response']      = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_query']         = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_reject']        = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_notify']        = 'meta_graph_dns_event';
    $MetaGraphDefs['dns_transfer']      = 'meta_graph_dns_event';

    if (function_exists('load_graph_definitions_local'))
        load_graph_definitions_local($logarithmic, $tinylegend, $zero);

    if ($logarithmic) {
        foreach ($GraphDefs as &$GraphDef) {
            array_unshift($GraphDef, '-o');
        }
    }
    if ($zero) {
        foreach ($GraphDefs as &$GraphDef) {
            array_unshift($GraphDef, '0');
            array_unshift($GraphDef, '-l');
            array_unshift($GraphDef, '-r');
        }
    }
    if ($tinylegend) {
        foreach ($GraphDefs as &$GraphDef) {
            for ($i = count($GraphDef)-1; $i >=0; $i--) {
                if (strncmp('GPRINT:', $GraphDef[$i], 7) == 0) {
                    unset($GraphDef[$i]);
                }
            }
        }
    }
}

function meta_graph_nb_values($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    global $config;
    $local_opts = array();

    if($plugin == "write_top") {
        switch($type_instances) {
            case 'nb_hosts':
                $opts['rrd_opts'] = array(
                        '-v', 'Nb Hosts',
                        '--units-exponent', '0',
                        );
                break;
            case 'nb_free_chunks':
                $opts['rrd_opts'] = array('-v', 'Nb Free memory chunks');
                break;
            case 'nb_tops_to_flush':
                $opts['rrd_opts'] = array('-v', 'Nb Top Ps to flush');
                break;
            default:
                if($type_instances) {
                    $opts['rrd_opts'] = array('-v', $type_instances);
                }
        }
    } else if($plugin == "jsonrpc") {
        $opts['rrd_opts'] = array(
                '-v', 'Nb metrics',
                '--units-exponent', '3',
                ); 
    }

    return collectd_draw_rrd($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, array_merge($opts, $local_opts));
}

function meta_graph_files_count($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {

    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['rrd_opts'] = array('-v', 'Mails');

    $opts['colors'] = array(
            'incoming' => '00e000',
            'active'   => 'a0e000',
            'deferred' => 'a00050'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_files_size($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {

    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['rrd_opts'] = array('-v', 'Bytes');

    $opts['colors'] = array(
            'incoming' => '00e000',
            'active'   => 'a0e000',
            'deferred' => 'a00050'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_df_complex($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {

    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '');
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts']      = array('-b', '1024', '-v', 'Bytes', '--units=si');

    $opts['colors'] = array(
            'used'      => '0000ff',
            'reserved'  => 'ffb000',
            'free'      => 'ff0000'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_cpufreq($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    global $config;
    $sources = array();

    $title = "$host/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    $title2 = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title2;
    $opts['number_format'] = '%5.3lf%s';
    $opts['rrd_opts'] = array('-v', 'Hz');
    $opts['colors'] = array(
            0   => '0000ff',
            1   => 'F7B7B7',
            2   => 'B7EFB7',
            3   => 'B7B7F7',
            4   => 'F3DFB7',
            5   => 'B7DFF7',
            6   => 'DFB7F7',
            7   => 'FFC782',
            8   => 'DCFF96',
            9   => '83FFCD',
            10  => '81D9FF',
            11  => 'FF89F5',
            12  => 'FF89AE',
            13  => 'BBBBBB',
            14  => 'ffb000',
            15  => 'ff0000'

            );
    /*
       $GraphDefs['irq'] = array(
       '-v', 'Issues/s',
       'DEF:avg={file}:value:AVERAGE',
       "LINE1:avg#$FullBlue:Issues/s",
       'GPRINT:avg:AVERAGE:%6.2lf Avg,',
       'GPRINT:avg:LAST:%6.2lf Last\l');
     */
    $sources = rrd_sources_from_files_sorted_by_type_instance($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_cache_entries($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    global $config;
    $sources = array();
    $title = "$host/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    $title2 = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title2;
    $opts['rrd_opts'] = array('-v', 'entries');
    $opts['colors'] = array(
            0   => '0000ff',
            1   => 'F7B7B7',
            2   => 'B7EFB7',
            3   => 'B7B7F7',
            4   => 'F3DFB7',
            5   => 'B7DFF7',
            6   => 'DFB7F7',
            7   => 'FFC782',
            8   => 'DCFF96',
            9   => '83FFCD',
            10  => '81D9FF',
            11  => 'FF89F5',
            12  => 'FF89AE',
            13  => 'BBBBBB',
            14  => 'ffb000',
            15  => 'ff0000'

            );

    $sources = rrd_sources_from_files_sorted_by_type_instance($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_irq($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    global $config;
    $sources = array();

    $title = "$host/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    $title2 = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title2;
    $opts['rrd_opts'] = array('-v', 'Issues/s');
    $opts['colors'] = array(
            0   => '0000ff',
            1   => 'F7B7B7',
            2   => 'B7EFB7',
            3   => 'B7B7F7',
            4   => 'F3DFB7',
            5   => 'B7DFF7',
            6   => 'DFB7F7',
            7   => 'FFC782',
            8   => 'DCFF96',
            9   => '83FFCD',
            10  => '81D9FF',
            11  => 'FF89F5',
            12  => 'FF89AE',
            13  => 'BBBBBB',
            14  => 'ffb000',
            15  => 'ff0000'

            );
    /*
       $GraphDefs['irq'] = array(
       '-v', 'Issues/s',
       'DEF:avg={file}:value:AVERAGE',
       "LINE1:avg#$FullBlue:Issues/s",
       'GPRINT:avg:AVERAGE:%6.2lf Avg,',
       'GPRINT:avg:LAST:%6.2lf Last\l');
     */

    $sources = rrd_sources_from_files_sorted_by_type_instance($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_users($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title2 = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title2;
    $opts['rrd_opts'] = array('-v', 'User(s)');
    $opts['colors'] = array(
            'users'      => '0000ff',
            'active'      => '0000ff',
            'inactive'    => 'ff0000'
            );

    $type_instances = array_keys($opts['colors']);
    $type_instances[] = "";
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_cpu($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    if (substr($plugin_instance, -4) == '_sum') {
        $opts['rrd_opts'] = array('-v', 'Percent', '-r');
    } else {
        $opts['rrd_opts'] = array('-v', 'Percent', '-r', '-u', '100');
    }

    $opts['colors'] = array(
            'idle'      => 'bbbbbb',
            'nice'      => '00e000',
            'user'      => '0000ff',
            'wait'      => 'ffb000',
            'system'    => 'ff0000',
            'softirq'   => 'ff00ff',
            'interrupt' => 'a000a0',
            'steal'     => '000000'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_celerra_io($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instance, $opts = array()) {
    global $config;
    $sources = array();
    $title = "$host/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    $title2 = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type".($type_instance != '' ? "-$type_instance" : '');
    if (!isset($opts['title']))
        $opts['title'] = $title2;
    switch($type) {
        case 'celerra_io':
            $opts['rrd_opts'] = array('-v', 'IO/s');
            break;
        case 'celerra_octets':
            $opts['rrd_opts'] = array('-v', 'Byte/s', '--units=si');
            break;
        case 'celerra_packetsize':
            $opts['rrd_opts'] = array('-v', 'Byte', '--units=si');
            break;
    }
    $opts['number_format'] = '%5.1lf%s';

    $opts['colors'] = array(
            'read_min'      => '0000ff',
            'read_avg'      => '0000af',
            'read_max'      => '00005f',
            'write_min'      => '00ff00',
            'write_avg'      => '00af00',
            'write_max'      => '005f00'
            );

    $files = rrd_get_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    while (list($k, $a) = each($files)) {
        $sources[] = array('name' => 'read_min', 'file' => $a[0], 'ds' => 'read_min');
        $sources[] = array('name' => 'read_avg', 'file' => $a[0], 'ds' => 'read_avg');
        $sources[] = array('name' => 'read_max', 'file' => $a[0], 'ds' => 'read_max');
        $sources[] = array('name' => 'write_min', 'file' => $a[0], 'ds' => 'write_min', 'reverse' => true);
        $sources[] = array('name' => 'write_avg', 'file' => $a[0], 'ds' => 'write_avg', 'reverse' => true);
        $sources[] = array('name' => 'write_max', 'file' => $a[0], 'ds' => 'write_max', 'reverse' => true);
        break; /* only 1st file is taken into account. Is this a bug ? */
    }

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_celerra_if($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    global $config;
    $sources = array();

    $title = "$host/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    $title2 = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title2;
    $opts['number_format'] = '%5.1lf%s';
    switch($type) {
        case 'celerra_if_errors':
            $opts['rrd_opts'] = array('-v', 'Error/s');
            break;
        case 'celerra_if_octets':
            $opts['rrd_opts'] = array('-v', 'Byte/s');
            break;
        case 'celerra_if_packets':
            $opts['rrd_opts'] = array('-v', 'Packet/s');
            break;
    }

    $opts['colors'] = array(
            'min_in'      => '0000ff',
            'avg_in'      => '0000af',
            'max_in'      => '00005f',
            'min_out'      => '00ff00',
            'avg_out'      => '00af00',
            'max_out'      => '005f00'
            );

    $type_instances = array('min', 'avg', 'max');
    $sources = array();

    $files = rrd_get_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    while (list($k, $a) = each($files)) {
        $sources[] = array('name' => $a[2].'_in', 'file' => $a[0], 'ds' => 'rx');
        $sources[] = array('name' => $a[2].'_out', 'file' => $a[0], 'ds' => 'tx', 'reverse' => true);
    }

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_swap_io($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['rrd_opts'] = array('-v', 'Pages/s');

    $opts['colors'] = array(
            'in'      => '0000ff',
            'out'      => 'ff0000'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_specs($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['rrd_opts'] = array('-v', 'Specs');

    $opts['colors'] = array(
            'cfp'      => '0000ff',
            'cint'      => 'ffb000'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_grid($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    //$opts['rrd_opts'] = array('-v', 'Specs');

    $opts['colors'] = array(
            'processes'      => '0000ff',
            'nodes' => 'ffb000',
            'load'     => 'ff0000',
            'cpus'      => '8888dd'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

# Append "$host/load_sum/load.rrd"
    $files = rrd_get_files($collectd_source, $host, "load_sum", "", "load", array(""));

    while (list($k, $a) = each($files)) {
        $sources[] = array('name'=> $a[2], 'file'=> $a[0], 'ds' => 'midterm');
    }
    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_memory($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts']      = array('-b', '1024', '-v', 'Bytes');

    $opts['colors'] = array(
            // Linux - System memory
            'free'     => '00e000',
            'cached'   => '0000ff',
            'buffered' => 'ffb000',
            'used'     => 'ff0000',
            'user'   => '00A0FF',
            // Solaris 10
            'kernel'   => 'F0A000',
            'system'   => 'F0A000',
            'unusable' => '0000ff',
            'locked'   => '00A0FF',
            // Bind - Server memory
            'TotalUse'    => '00e000',
            'InUse'       => 'ff0000',
            'BlockSize'   => '8888dd',
            'ContextSize' => '444499',
            'Lost'        => '222222',
            // Windows
            'available'     => '34B3A2',
            'pool_paged'    => 'C82A6F',
            'pool_nonpaged' => '390FC4',
            'system_cache'  => '9B7909',
            'code'          => '5A8727',
            'driver'        => '48E2D3',
            'working_set'   => 'F77F87',
            // FreeBSD - System memory
            'active'        => 'ff0000',
            'cache'         => '0000ff',
            'inactive'      => '00a0ff',
            'wired'         => 'f0a000',
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    if ($plugin == 'bind')
        return collectd_draw_meta_line($opts, $sources);
    else
        return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_vs_threads($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts']      = array('-v', 'Threads');

    $opts['colors'] = array(
            'total'   => 'F0A000',
            'running'  => 'FF0000',
            'onhold'  => '00E000',
            'uninterruptable' => '0000FF'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_vs_memory($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts']      = array('-b', '1024', '-v', 'Bytes');

    $opts['colors'] = array(
            'vm'   => 'F0A000',
            'vml'  => 'FF0000',
            'rss'  => '00E000',
            'anon' => '0000FF'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_if_rx_errors($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%5.2lf';
    $opts['rrd_opts']      = array('-v', 'Errors/s');

    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_mysql_threads($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['rrd_opts'] = array('-v', 'Issues/s');
    $opts['number_format'] = '%5.2lf';

    $type_instances = array('cached','connected','running');
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_mysql_commands($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['rrd_opts'] = array('-v', 'Issues/s');
    $opts['number_format'] = '%5.2lf';

    $type_instances = array('admin_commands','alter_table','change_db','delete','flush','insert','insert_select','kill','lock_tables','optimize','repair','replace','select','set_option','show_binlogs','show_charsets','show_collations','show_create_db','show_create_table','show_databases','show_fields','show_grants','show_keys','show_master_status','show_plugins','show_processlist','show_slave_status','show_status','show_storage_engines','show_tables','show_table_status','show_triggers','show_variables','truncate','unlock_tables','update','commit','read_first','read_key','read_next','read_rnd_next','read_rnd','rollback','write');
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_nfs_procedure($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts'] = array('-v', 'Ops/s');

    $type_instances = array('access','commit','create','fsinfo','fsstat','getattr','link','lookup','mkdir','mknod','null','pathconf','readdirplus','readdir','readlink','read','remove','rename','rmdir','setattr','symlink','write');
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_ps_state($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['rrd_opts'] = array('-v', 'Processes');

    $opts['colors'] = array(
            'running'  => '00e000',
            'runnable'  => '00e000',
            'sleeping' => '0000ff',
            'paging'   => 'ffb000',
            'zombies'  => 'ff0000',
            'I_J_Z'  => 'ff0000',
            'blocked'  => 'ff00ff',
            'stopped'  => 'a000a0'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_swap($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts']      = array('-b', '1024', '-v', 'Bytes');

    $opts['colors'] = array(
            'free'     => '00e000',
            'cached'   => '0000ff',
            'used'     => 'ff0000'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_apache_scoreboard($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%6.2lf%s';
    $opts['rrd_opts']      = array('-v', 'Processes');

    $opts['colors'] = array(
    /*        'open'         => '00e000',*/
            'waiting'      => '0000ff',
            'starting'     => 'a00000',
            'reading'      => 'ff0000',
            'sending'      => '00ff00',
            'keepalive'    => 'f000f0',
            'dnslookup'    => '00a000',
            'logging'      => '008080',
            'closing'      => 'a000a0',
            'finishing'    => '000080',
            'idle_cleanup' => '000000',
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_tcp_connections($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['number_format'] = '%6.2lf%s';
    $opts['rrd_opts']      = array('-v', 'Connections');

    $opts['colors'] = array(
            'ESTABLISHED' => '00e000',
            'SYN_SENT'    => '00e0ff',
            'SYN_RECV'    => '00e0a0',
            'FIN_WAIT1'   => 'f000f0',
            'FIN_WAIT2'   => 'f000a0',
            'TIME_WAIT'   => 'ffb000',
            'CLOSE'       => '0000f0',
            'CLOSE_WAIT'  => '0000a0',
            'LAST_ACK'    => '000080',
            'LISTEN'      => 'ff0000',
            'CLOSING'     => '000000'
            );

    $type_instances = array_keys($opts['colors']);
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

function meta_graph_dns_event($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
    $title = ((isset($opts['althost']) && $opts['althost'])?$opts['althost']:get_node_name($host))."/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
    if (!isset($opts['title']))
        $opts['title'] = $title;
    $opts['rrd_opts'] = array('-v', 'Events', '-r', '-l', '0');

    //	$type_instances = array('IQUERY', 'NOTIFY');
    $sources = rrd_sources_from_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);
    return collectd_draw_meta_stack($collectd_source, $opts, $sources);
}

?>
