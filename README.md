# NivoCart

 [![Codacy Badge](https://app.codacy.com/project/badge/Grade/f03f3fae6e5e4788aadd87d48b443d99)](https://app.codacy.com/gh/nivocart/nivocart/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
 [![Maintainability](https://qlty.sh/gh/nivocart/projects/nivocart/maintainability.svg)](https://qlty.sh/gh/nivocart/projects/nivocart)
 [![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](https://github.com/nivocart/nivocart/issues)
 [![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
 [![BuyMeACoffee](https://raw.githubusercontent.com/pachadotdev/buymeacoffee-badges/main/bmc-donate-yellow.svg)](https://www.buymeacoffee.com/nivocart)


ADVANCED SHOPPING CART SYSTEM

NivoCart is a powerful all-in-one shopping cart application for small and medium businesses.<br />
It uses the MVC+L (Model-View-Controller + Language) architecture and is written in pure PHP, Html5, CSS3, jQuery.<br />
NivoCart has been designed to be fast, completely self-contained, and without any remote dependencies.<br />
it doesn't use a framework or a template engine, and can be easily customised.<br />

_____________________________________________________________________________________________

DEMONSTRATION: Click <a href="https://nivocart.org/index.php?route=demonstration/demonstration" title="Demos">HERE</a> to test the current version of NivoCart.
_____________________________________________________________________________________________

COMMUNITY: The NivoCart community proudly lives on Fluxer! Find it <a href="https://web.fluxer.app/channels/1488046840332602753/" title="NivoCart Fluxer">HERE</a> to get involved!
_____________________________________________________________________________________________


REQUIREMENTS:

- Server: Apache Linux Server
- Database: MySQLi / MariaDB
- Extensions: OpenSSL Encrypt + cURL
- PHP: PHP 8.1+

_____________________________________________________________________________________________


INSTALLATION:

- 1 - Download the latest version of NivoCart.
- 2 - Upload the zip archive directly to your server (can be local or live).
- 3 - Unzip the archive in a temporary folder of your choice.
- 4 - Copy/Paste the content of the "upload" folder at the root of your domain.
- 5 - Find the 2 "config-dist.php" files (root and admin/) and rename them "config.php".
- 6 - Create an empty database on your server and give your username all privileges.
- 7 - Run the installer by entering "&lt;your domain&gt;/install/index.php" in your browser.
- 8 - Once you have completed the installer, simply login into your new Admin!

Note: The installation folder (install/) will be automatically deleted after a successful install.

 
UPGRADE:

Same as the above INSTALLATION guidelines, but without steps 5 and 6:
- Do Not overwrite your "config.php" files!  ... just Skip step 5.
- No need for a new database.  ... just Skip step 6.

Then, when running step 7, you will be presented with the Upgrade page instead.

Just click "Upgrade". Done!

_____________________________________________________________________________________________


A LITTLE BIT OF HISTORY:

The NivoCart project started back in 2017 as an improved clone of OpenCart&trade; shopping cart.
It was originally based on OpenCart&trade; v1.5.6.4 because of its logical folder structure and simple Html.
Later versions of OpenCart&trade; added the Bootstrap framework and the Twig template engine,
which I guess was the right thing to do at the time, but made the code over-complicated in my view.
This is when I decided to "make" NivoCart using plain and simple Html5, CSS3 and jQuery.
The initial v1.0.x versions of NivoCart added many new features and "quality of life" (QoL) improvements
over the reference code, it also used stricter coding standards and updated jQuery scripts.
However, as the project grew stronger, I started to be aware of some underlying bugs I couldn't fix.
Then the Covid years came along and the NivoCart project was on pause for a few years.
Comes 2026 and a new AI Agents era! Suddenly I realised that I could fix those bugs with AI help.
So I went back to my NivoCart code and started refreshing it to the new standards of today.
Many years had passed, so there was a lot to do. I will spare you all the details here but as of today,
I can confidently say that all known bugs have been fixed and the code is as strong as it has ever been.
NivoCart v2.0.0 will be a very solid base for the future and I hope it will be useful to some of you.

_____________________________________________________________________________________________


SELF-CONTAINED:

While NivoCart has been designed to be fully self-contained and not relying on remote connections,
there are some unavoidable exceptions to be aware of, such as:

- Payment Gateways: such as PayPal, Stripe, Klarna and Sage Pay, will initiate remote connections
  (as expected) to verify payments.

- Currency updates (default): once a day, NivoCart will attempt to connect to "www.floatrates.com",
  to get the latest exchange rates: 'https://www.floatrates.com/daily/'

- Currency updates with Alpha Vantage (optional):
  Alpha Vantage (Currency_Exchange_Rate API) is optional, but would also initiate a remote connection.

- Share This (optional): Share This is a social media sharing service integrated into NivoCart.
  By design, it will create a remote connection to Share This, if you choose to use it.

- YouTube (optional): Product pages and some modules can display YouTube video codes.

_____________________________________________________________________________________________


VERSIONING:

NivoCart adheres to the Semantic Versioning guidelines.<br />
Releases will be numbered as follow:
<p><code>&lt;major&gt;.&lt;minor&gt;.&lt;patch&gt;</code></p>
For more information, please visit <a href="https://semver.org" rel="nofollow">https://semver.org</a>.

_____________________________________________________________________________________________


WANT TO CONTRIBUTE?
- Test and report bugs, typos or improvements.
- Design new Extensions and Themes.
- Suggest new features.
- Star the project.


HOME: <a href="https://nivocart.org" title="Home">NivoCart Home</a>

EMAIL: contact@nivocart.org


Copyright &copy; 2026 - nivocart.org
