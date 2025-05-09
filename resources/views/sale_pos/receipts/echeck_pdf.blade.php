<style type="text/css">    
        @import url(https://fonts.googleapis.com/css?family=Damion);
        @import url(https://fonts.googleapis.com/css?family=Mrs+Saint+Delafield);
        @font-face{
            font-family:"micr-emicren-regular";
            src:url("{{ asset('fonts/micr-encoding.regular.ttf') }}") format("woff"),
            url("{{ asset('fonts/micr-encoding.regular.ttf') }}") format("opentype"),
            url("{{ asset('fonts/micr-encoding.regular.ttf') }}") format("truetype");
        }
</style>
              <table width="100%">
                        <tr>
                            <td style="vertical-align: top;text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;padding-left: 130px;" width="40%">
                                <span style="text-transform:uppercase;">{{!empty($receipts['name']) ? $receipts['name'] : 'Your Name' }}</span> <br>
                                <span style="text-transform:uppercase;">@if(!empty($receipts['address1'])) {{ $receipts['address1'] }} <br> @endif

                                @if(!empty($receipts['address2'])) {{ $receipts['address2'] }} <br> @endif
                                
                                @if(!empty($receipts['address3'])) {{ $receipts['address3'] }} @endif
                                 </span>
                            </td>
                            <td style="vertical-align: top;text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;padding-left: 130px;" width="50%">
                                <span style="vertical-align: top;text-transform: uppercase;">{{!empty($receipts['bname']) ? $receipts['bname'] : 'Bank Name' }}</span> <br>
                                <span style="text-transform:uppercase;">@if(!empty($receipts['baddress1'])) {{ $receipts['baddress1'] }} <br> @endif

                                @if(!empty($receipts['baddress2'])) {{ $receipts['baddress2'] }} <br> @endif
                                
                                @if(!empty($receipts['baddress3'])) {{ $receipts['baddress3'] }} @endif
                                 </span>
                            </td>
                            <td style="vertical-align: top;text-align: right;font-family: 'Times New Roman', serif;font-size:10pt;">
                                <span>No. {{!empty($receipts['cno']) ? $receipts['cno'] : '1013' }}</span><br>
                                <span style="font-size: 10px;">{{!empty($receipts['tcode']) ? $receipts['tcode'] : '' }}</span><br>
                            </td>
                        </tr>
                    </table>
                    <table width="100%">
                        <tr style="vertical-align: middle;">
                            <td style="text-align: right;font-family: 'Times New Roman', serif;font-size:10pt;">
                                <span>Date&nbsp;&nbsp;&nbsp;</span>
                                <span>@if(!empty($receipts['edate'])) {{ $receipts['edate'] }} @else 10/20/2022 @endif</span>
                            </td>
                        </tr>
                    </table>

                    <table width="100%" style="margin-top: 20px;">
                        <tr> 
                            <td style="vertical-align: bottom;text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;padding-left: 10px;" width="12%">Pay To The Order Of</td>
                            <td style="vertical-align: bottom;text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;border-bottom: 1px solid #000;" width="65%">@if(!empty($receipts['rname'])) {{ $receipts['rname'] }} @else Bhagat Inc. @endif
                            </td>
                            <td style="vertical-align: bottom;text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;">
                                $&nbsp;@if(!empty($receipts['final_amount'])) {{ $receipts['final_amount'] }} @else **1253.23 @endif
                            </td>
                        </tr>
                    </table>
                    <table width="100%" style="margin-top: 10px;">
                        <tr style="vertical-align: bottom;">
                            <td style="vertical-align: bottom;text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;border-bottom: 1px solid #000;">
                                <span>
                                @if(!empty($receipts['amtstring'])) {{ $receipts['amtstring'] }} @else One Thousand Two Hundred Thirty-Five and 23 /100 @endif
                                 </span>
                            </td>
                            <td style="vertical-align: bottom; text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;margin-top: 2px;" width="20%">
                                Dollars
                            </td>
                        </tr>
                    </table>
                    <table width="100%" style="margin-top: 20px;">
                        <tr>
                            <td style="text-align: right;vertical-align: middle;font-family: Helvetica;font-size: 16px;">
                                <span>This draft authorized by your depositor</span><br>
                            </td>
                        </tr>
                    </table>
                    <table width="100%">
                        <tr>
                            <td style="vertical-align: bottom;text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;" width="7%">
                                <span>Memo:</span>
                            </td>
                            <td style="vertical-align: bottom;text-align: left;font-family: 'Times New Roman', serif;font-size:10pt;border-bottom: 1px solid #000;" width="38%">
                                <span>@if(!empty($receipts['memo'])) {{ $receipts['memo'] }} @else Test Demo @endif</span>
                            </td>
                            <td style="text-align: right;vertical-align: middle;font-family: Helvetica;" width="15%">
                                &nbsp;
                            </td>
                            <td style="text-align: center;vertical-align: middle;font-family: Helvetica;border-bottom: 1px solid #000;padding-right: 5px;">
                                <span style="font-size: 18px; font-weight: bold;">NO SIGNATURE REQUIRED</span>

                            </td>
                        </tr>
                    </table>
                    <table width="100%" style="margin-top: 10px;">
                        <tr>
                            <td style="font-family:micr-emicren-regular;vertical-align: bottom;text-align: left;font-size:22px;font-weight: bold;padding-left: 100px;">
                                <span>C{{!empty($receipts['cnobottom']) ? $receipts['cnobottom'] : '000001013' }}C</span>
                                <span>A{{!empty($receipts['rno']) ? $receipts['rno'] : '7890253256' }}A{{!empty($receipts['anumber']) ? $receipts['anumber'] : '987456321789632589' }}C</span>
                            </td>
                        </tr>
                    </table>