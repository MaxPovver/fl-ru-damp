<!--{include file='control/header_admin.tpl'}-->
<div class="menu">
	<!--{include file='control/top_block.tpl'}-->
	<!--{get_res code="page.visit.intro"}-->
</div>
<div class="info-box">
    <div class="visit-info">
        <table>
            <!--{if $page_settings.enterref}-->
            <tr>
                <td class="table"><!--{get_res code="page.visit.referrer"}--></td><td width="3"></td>
                <td class="table"><a href="<!--{$page_settings.enterref}-->"><!--{$page_settings.enterref}--></a>
            </tr>
            <!--{/if}-->

            <tr>
                <td class="table"><!--{get_res code="page.visit.enter.point"}--></td><td width="3"></td>
                <td class="table"><a href="<!--{$page_settings.landingpage}-->"><!--{$page_settings.landingpage}--></a></td>
            </tr>
            <tr>

            <!--{if $page_settings.active}-->
                <td class="table"><!--{get_res code="page.visit.current.page"}--></td><td width="3"></td>
            <!--{else}-->
                <td class="table"><!--{get_res code="page.visit.leave.point"}--></td><td width="3"></td>
            <!--{/if}-->
                <td class="table">
                    <a href="<!--{$page_settings.exitpage}-->"><!--{$page_settings.exitpage}--></a>
                </td>
            </tr>
            <tr>
                <td class="table"><!--{get_res code="page.visit.vistime"}--></td><td width="3"></td>
                <td class="table"><!--{$page_settings.timediff}--></a> </td>
            </tr>
            <tr>
                <td class="table"><!--{get_res code="page.visit.browser"}--></td><td width="3"></td>
                <td class="table"><!--{$page_settings.browser}--></td>
            </tr>
            <tr>
                <td class="table"><!--{get_res code="page.visit.ip"}--></td><td width="3"></td>
                <td class="table">
                    <!--{$page_settings.ip}--> <br/>
                        <!--{if $page_settings.country && $page_settings.city}-->
                            <a href="http://maps.google.com/maps?q=<!--{$page_settings.lat}-->, <!--{$page_settings.lng}-->" target="_blank">
                                <!--{$page_settings.city}-->, <!--{$page_settings.country}-->
                            </a>
                        <!--{/if}--> 
                </td>
            </tr>
        </table>

        <p>
            <a href="<!--{add_params servlet_root=$webim_root servlet='/operator/history.php' path_vars=$page_settings.historyParams}-->" target="_blank" title="<!--{get_res code="page_analysis.search.title"}-->"><!--{get_res code="page.visit.previous.chats"}--></a>
        </p>
        <!--{if $isStatisticsAvailable}-->
        <p>
            <a href="/bitrix/admin/guest_list.php?lang=ru&set_filter=Y&find_user=<!--{$userid}-->&find_user_exact_match=Y&find_user_exact_match=Y&find_country_exact_match=Y" target="_blank"><!--{get_res code="page.visit.statistic"}--></a>
        </p>
        <!--{/if}-->
    </div>

    <div class="listof">
        <h2><!--{get_res code="page.visit.visited.pages"}--></h2>
        <table class="pending-visitors">
            <tr>
                <th><!--{ get_res code='page.visit.pagetime'}--></th>
                <th><!--{ get_res code='page.visit.url'}--></th>
                <th class="last"><!--{ get_res code='page.visit.referrer'}--></th>
            </tr>
            <!--{foreach from=$page_settings.visitedpages item=p}-->
            <tr>
                <td><!--{$p.opened|date_format:"%b, %d %Y %H:%M:%S"}-->, <!--{$p.sessionduration|date_diff}--></td>
                <td><a href="<!--{$p.uri}-->"><!--{$p.uri}--><a></td>
                <td class="last"><a href="<!--{$p.referrer}-->"><!--{$p.referrer}--><a></td>
            </tr>
            <!--{/foreach}-->
        </table>
    </div>
</div>
<!--{include file='control/footer.tpl'}-->
