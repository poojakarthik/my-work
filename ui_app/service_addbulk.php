
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns:dt="http://xsltsl.org/date-time">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>viXen : Employee Intranet System</title>
    <link rel="stylesheet" type="text/css" href="css.php" />
	
    
    <script language="javascript" src="javascript/lightbox/jquery-latest.js"></script>
    <script language="javascript" src="javascript/lightbox/dimensions.js"></script>
    <script language="javascript" src="javascript/lightbox/jquery-modalContent.js"></script>
  </head>
  <body>
    <div class="Logo">
      <img src="img/template/vixen_logo.png" border="0" />
    </div>
    <div id="Header" class="sectionContainer">
      <span class="LogoSpacer"></span>
      <div class="sectionContent">
        <div class="Left">
									TelcoBlue Internal Management System
								</div>
        <div class="Right">
									Version 7.03
									
									<div class="Menu_Button"><a href="#" onclick="return ModalDisplay ('#modalContent-ReportBug')"><img src="img/template/bug.png" border="0" alt="Report Bug" title="Report Bug" /></a></div><div id="modalContent-ReportBug"><div class="modalContainer"><div class="modalContent"><form method="post" name="bugreport" id="bugreport" action="bug_report.php" onsubmit="return BugSubmit(this)"><input type="hidden" name="SerialisedGET" value="a:1:{s:7:&quot;Account&quot;;s:10:&quot;1000154803&quot;;}" /><input type="hidden" name="SerialisedPOST" value="a:0:{}" /><table border="0" cellpadding="0" cellspacing="0"><tr><td valign="top" width="100%"><h1>Bug Report</h1>
																Please describe the problem that occurred :
																
																<textarea name="Comment" style="width: 725px; height: 225px;" class="input-summary-note"></textarea><div class="Right"><input type="button" value="Report Bug &#xBB;" onclick="javascript:document.forms['bugreport'].submit()" class="input-submit" /></div></td></tr></table></form></div><div class="modalTitle"><div class="modalIcon Left"><img src="img/template/lady-debug.png" /></div><div class="modalLabel Left"><strong>Report a System Bug</strong><br />
												Let us know when something isn't working the way you expect
											</div><div class="modalClose Right"><img src="img/template/closelabel.gif" class="close" /></div><div class="Clear"></div></div></div></div></div>
        <div class="Clear"></div>
      </div>
      <div class="Clear"></div>
    </div>
    <div class="Clear"></div>
    <div class="Seperator"></div>
    <div id="Content">
      <table border="0" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td valign="top" width="75" nowrap="nowrap">
            <div id="Navigation" class="Left sectionContent Navigation">
              <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <a href="console.php">
                      <img src="img/template/home.png" title="Employee Console" class="MenuIcon" />
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href="account_add.php">
                      <img src="img/template/contact_add.png" title="Add Customer" class="MenuIcon" />
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href="contact_verify.php">
                      <img src="img/template/contact_retrieve.png" title="Find Customer" class="MenuIcon" />
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href="#" onclick="return ModalDisplay ('#modalContent-recentCustomers')">
                      <img src="img/template/history.png" title="Recent Customers" class="MenuIcon" />
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href="rates_plan_list.php">
                      <img src="img/template/plans.png" title="View Available Plans" class="MenuIcon" />
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href="console_admin.php">
                      <img src="img/template/admin_console.png" title="Administrative Console" class="MenuIcon" />
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href="#" onclick="return Logout()">
                      <img src="img/template/logout.png" title="Logout" class="MenuIcon" />
                    </a>
                  </td>
                </tr>
              </table>
            </div>
          </td>
          <td valign="top">
            <div id="modalContent-Popup">
              <div class="modalContainer">
                <div class="modalContent" id="Modal-Popup-Content"></div>
                <div class="modalTitle">
                  <div class="modalIcon Left">
                    <img id="Modal-Popup-Icon" src="" />
                  </div>
                  <div class="modalLabel Left">
                    <strong id="Modal-Popup-Title"></strong>
                    <br />
                    <span id="Modal-Popup-Summary"></span>
                  </div>
                  <div class="modalClose Right">
                    <img src="img/template/closelabel.gif" class="close" />
                  </div>
                  <div class="Clear"></div>
                </div>
              </div>
            </div>
            <ul id="QuickList" class="Right">
              <li>
                <a href="account_view.php?Id=1000154803">View Account</a>
              </li>
            </ul>
            <h1>Add Service</h1>
            <script language="javascript" src="javascript/service_add_input.js" onload="Init()"></script>
            <script language="javascript" src="javascript/ABN.js"></script>
            
            <script language="javascript" src="javascript/ajax.js"></script>
            <form method="POST" action="service_addbulk.php" onsubmit="return Validate()">
              <input type="hidden" name="Account" id="Account" value="1000154803" />
              <h2 class="Account">Account Details</h2>
              <div class="Wide-Form">
                <div class="Form-Content">
                  <table border="0" cellpadding="3" cellspacing="0">
                    <tr>
                      <th class="JustifiedWidth"><a href="#" class="Label" alt="Pablo provides helpful online documentation" title="Pablo 'the helpful donkey'" onclick="return ModalExternal (this, 'documentation_view.php?Entity=Account&amp;Field=Id')">Account ID</a> :
	</th>
                      <td>1000154803</td>
                    </tr>
                    <tr>
                      <th class="JustifiedWidth"><a href="#" class="Label" alt="Pablo provides helpful online documentation" title="Pablo 'the helpful donkey'" onclick="return ModalExternal (this, 'documentation_view.php?Entity=Account&amp;Field=BusinessName')">Business Name</a> :
	</th>
                      <td>Telco Blue Pty Ltd</td>
                    </tr>
                  </table>
                </div>
              </div>
              <div class="Seperator"></div>
              <h2 class="Service">Service Details</h2>
              <div class="Wide-Form">
                <div class="Form-Content">
                  <table border="0" cellpadding="3" cellspacing="0" id="thetable">
                    <select style="display:none" id="hiddenCostCentres">
                      <option value=""></option>
                      <option value="174">Head Office</option>
                      <option value="175">1300/1800 numbers</option>
                    </select>
                    <select style="display:none" id="hiddenPlans100">
                      <option value=""></option>
                      <option value="100000000">@ Home</option>
                      <option value="8">Demo</option>
                    </select>
                    <select style="display:none" id="hiddenPlans101">
                      <option value=""></option>
                      <option value="11">$35 Cap</option>
                      <option value="13">Blue Shared 100</option>
                      <option value="14">Blue Shared 250</option>
                      <option value="15">Blue Shared 500</option>
                      <option value="19">Fleet 20</option>
                      <option value="18">Fleet 30</option>
                      <option value="17">Fleet 60</option>
                      <option value="16">Fleet Special Peter K</option>
                      <option value="20">Pinnacle</option>
                      <option value="12">Plan Ten</option>
                      <option value="9">Plan Zero</option>
                    </select>
                    <select style="display:none" id="hiddenPlans102">
                      <option value=""></option>
                      <option value="22">Blue 15 CTM</option>
                      <option value="21">Blue 39c Cap</option>
                      <option value="24">Bus Saver Capped</option>
                      <option value="27">National 16</option>
                      <option value="33">Peter K Group Special</option>
                      <option value="32">Pinnacle</option>
                      <option value="29">Residential</option>
                      <option value="25">Tier Three Corporate Capped</option>
                      <option value="30">Tier Three Local Saver</option>
                      <option value="36">Tier Three Long Distance</option>
                      <option value="31">Tier Three Mobile Saver</option>
                      <option value="26">True Blue Fleet</option>
                      <option value="23">Virtual VOIP</option>
                      <option value="28">Voicetalk Capped</option>
                    </select>
                    <select style="display:none" id="hiddenPlans103">
                      <option value=""></option>
                      <option value="34">Inbound1300</option>
                      <option value="35">Inbound1800</option>
                    </select>
                    <tbody id="inputs">
                      <tr>
                        <td></td>
                        <th class="JustifiedWidth" style="width:120px"><strong><span class="Red">*</span></strong><a href="#" class="Label" alt="Pablo provides helpful online documentation" title="Pablo 'the helpful donkey'" onclick="return ModalExternal (this, 'documentation_view.php?Entity=Service&amp;Field=FNN')">Service #</a> :
	</th>
                        <th class="JustifiedWidth" style="width:120px"><strong><span class="Red">*</span></strong><a href="#" class="Label" alt="Pablo provides helpful online documentation" title="Pablo 'the helpful donkey'" onclick="return ModalExternal (this, 'documentation_view.php?Entity=Service&amp;Field=RepeatFNN')">Confirm Service #</a> :
	</th>
                        <th id="servicetype"></th>
                        <th class="JustifiedWidth"><a href="#" class="Label" alt="Pablo provides helpful online documentation" title="Pablo 'the helpful donkey'" onclick="return ModalExternal (this, 'documentation_view.php?Entity=Service&amp;Field=CostCentre')">Cost Centre</a> :
	</th>
                        <th class="JustifiedWidth"><strong><span class="Red">*</span></strong><a href="#" class="Label" alt="Pablo provides helpful online documentation" title="Pablo 'the helpful donkey'" onclick="return ModalExternal (this, 'documentation_view.php?Entity=Rate Plan&amp;Field=SelectPlan')">Select Plan</a> :
	</th>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="SmallSeperator"></div>
              <div class="Left"><strong><span class="Red">* </span></strong>: Required field<br /></div>
              <div class="Right">
                <input type="button" value="More" class="input-submit" onclick="AddManyInput(1);" />
                <input type="button" value="Submit &#xBB;" class="input-submit" onclick="Submit();" />
              </div>
              <div class="Seperator"></div>
              <div class="Seperator"></div>
                       </td>
        </tr>
      </table>
    </div>
    <div id="modalContent-recentCustomers">
      <div class="modalContainer">
        <div class="modalContent">
          <h1>Recent Customers</h1>
          <table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
            <thead>
              <tr class="First">
                <th width="30">#</th>
                <th>Primary Account Name</th>
                <th>First Name</th>
                <th>Last Name</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <div class="MsgNoticeModal">
									You have no Recently Verified Customers.
								</div>
        </div>
        <div class="modalTitle">
          <div class="modalIcon Left">
            <img src="img/template/history.png" />
          </div>
          <div class="modalLabel Left"><strong>Recent Customers</strong><br />
								Your 20 most recently verified customers
							</div>
          <div class="modalClose Right">
            <img src="img/template/closelabel.gif" class="close" />
          </div>
          <div class="Clear"></div>
        </div>
      </div>
    </div>
  </body>
</html>
