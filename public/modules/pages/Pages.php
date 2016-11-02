<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Home
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Pages {

    function initEnv() {
        Toro::addRoute(["/" => 'Pages']);
        Toro::addRoute(["/:alpha" => 'Pages']);
    }

    private $registered_pages = ['home', 'about', '7dtd', 'imprint', 'impressum', 'privacy', 'datenschutz'];

    function get($alpha = NULL) {
        $page = Page::getInstance();


        if ($alpha !== NULL AND !in_array($alpha, $this->registered_pages)) {
            header("Location: /");
        }
        switch ($alpha) {
            default: // Home
            case "home" :
                $page->setContent('{##main##}', '<h2>Home</h2>');
                $page->addContent('{##main##}', '<p>Welcome to the EoL Guild-Home</p>');
                $page->addContent('{##main##}', '<p>This website is a work in progress. Expect bugs and broken stuff :)</p>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Registrations are open for guildies, please speak to an officer about it. Preferably Evo or Ani.<br />');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', '<a href="/register">Create your website account now</a> to shout out to your guildies, create events or check out your upcoming character-birthdays.<br />');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Now is your chance to participate in the development process: Help us make this website great and use the opportunity to steer this thing in a direction you like.');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<p>Come online and listen to the silence of loneliness on ts3.notjustfor.me!</p>');
                break;
            case "about" :
                $page->setContent('{##main##}', '<h2>About</h2>');

                $page->addContent('{##main##}', '<h3>What is EoL?</h3>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'It’s the Evolution of Loneliness! All clear now? No?? Come on! Okay, we’ll explain a bit more in detail:
        This guild was started as a bank guild by Evo and soon already became accessible for Ani. It began to feel empty and under its usefulness, so we started recruiting some people for our mini guild. Although we first thought nobody would join us we found some lovely people joining EoL. And that was when the guild as a real guild was born.');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Evolution of Loneliness not only sounds beautiful, it has a nice meaning for us. Usually a player is alone in front of his computer, but when joining the right guild and getting connected to like-minded people to have fun with this loneliness is somehow improving and transformed into something still lonely but not thaaat lonely anymore -> Evolution of Loneliness! (makes sense, doesn’t it? ;))');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'We are a casual-style guild with both experienced and newer players. Our members are active in nearly all aspects of the game, only WvW gets very low attention. If we sometimes do the WvW missions, it will happen on Aurora Glade.. 
        We do have a main focus, but it’s not a game mode like pvp or fractals. It’s being social, nice and helpful, a friendly community where everyone feels welcome. This is also why we like being smaller-sized and getting to know each other. We don’t want to be a huge 300+ guild where we have no idea about the persons behind the names in the roster and guild missions feel like a big pug event.');
                $page->addContent('{##main##}', '</p>');

                $page->addContent('{##main##}', '<h3>EoL rules</h3>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Well, rules sounds so harsh, it’s more the main thing EoL stands for ;) We don’t have a representation rule nor any hard guidelines about participating or stuff. You just do what you want while being online - and sometimes together.
        The only thing we ask you is being nice and friendly to other guildies as well as strangers. We are not the kind of people trolling other or raging in pvp because someone messed something up. We are more the kind of people running for a rezz although we know it might be our own end ;)');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'If you have read the first chapter ‘What is EoL’ (well done! ;)) you know we have our focus on socializing with the guild and its members. So we want you to make sure EoL gets a bit of your online time - it doesn’t even need to be the main time. Have your WvW/PvP/Raid/whatever guilds, noone will tell you how to play a game you payed for in your free time. But: We also don’t want a name nobody knows in the roster or just one member more to raise the count, so please make sure giving EoL guildies the opportunity to get to know you. Talk in chat, participate in guild missions, show up at special events you like, it’s totally up to what you do - just get involved.');
                $page->addContent('{##main##}', '</p>');

                $page->addContent('{##main##}', '<h3>Ranks and responsibilities</h3>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Like every guild EoL has ranks too :) Here is a list of the current rank titles with their corresponding meanings and permissions:');
                $page->addContent('{##main##}', '</p>');

                $page->addContent('{##main##}', '<table><tbody>'
                        . '<tr><th><p><span>Rank name</span></p></th><th><p><span>Description / Permissions</span></p></th><th><p><span>How to get there?</span></p></th></tr>'
                        . '<tr><td><p><span>Leader</span></p></td><td><p><span>It’s the leader - all! ;)</span></p></td><td><p><span>Well, you need to found the guild or be girlfriend of the founder ;)</span></p></td></tr>'
                        . '<tr><td><p><span>Officer</span></p></td><td><p><span>Officers help the leaders with ‘leading’ and organizing the guild. They have nearly every permission including member administration and mission control.</span></p></td><td><p><span>You have to be chosen by the leaders themselves.</span></p></td></tr>'
                        . '<tr><td><p><span>Chieftain</span></p></td><td><p><span>A chieftain is a member that has officially taken some extra responsibility. Has member rights plus being able to open guild portals and manage guild teams.</span></p></td><td><p><span>You have to be chosen by leaders and/or being noticed by taking extra effort/responsiblity for a certain area.</span></p></td></tr>'
                        . '<tr><td><p><span>Member</span></p></td><td><p><span>Regular guild member. Permissions include decorating guild hall, using guild bank and claiming WvW objects.</span></p></td><td><p><span>You have to get involved with EoL, sign up at the website, take part in events, just be there and give the guild a chance to get to know you :)</span></p></td></tr>'
                        . '<tr><td><p><span>Recruit</span></p></td><td><p><span>This is where new members start. Can deposit into guild bank but not withdraw.</span></p></td><td><p><span>Join the guild by contacting either Ani (Ani.8473) or any other officer preferably ingame. Will then have a little talk and check out if we could fit together.</span></p></td></tr>'
                        . '<tr><td><p><span>Visitor</span></p></td><td><p><span>A visitor is a member with rare attention for EoL. Same permissions as recruits have.</span></p></td><td><p><span>Mostly represent other guilds and infrequently participate in EoL events. &nbsp;</span></p></td></tr>'
                        . '<tr><td><p><span>Slacker</span></p></td><td><p><span>This guildie hasn’t seen for a longer time. Same permissions as recruits have.</span></p></td><td><p><span>Do not log in for a month - not recommended ;)</span></p></td></tr>'
                        . '<tr><td><p><span>Phantom</span></p></td><td><p><span>This guildie hasn’t even seen for a loooonger time.</span></p></td><td><p><span>Do not log in for three months - really not recommended ;)</span></p></td></tr>'
                        . '<tr><td><p><span>Unkickable</span></p></td><td><p><span>This guildy has probably quit for good. But still...</span></p></td><td><p><span>Quit the game for whatever reason. Do a big deed for the guild, get into the hearts of us all and be remembered forever. This guildie will always have an open spot in out roster and will NEVER be kicked, denied or forgotten!</span></p></td></tr>'
                        . '</tbody></table>');

                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Beside the ranks, it’s good to know we have some officers/chieftains who have taken over an area responsibility, so these persons are ‘THE person’ to ask when you have questions/ideas/critic/whatever to that areas:');
                $page->addContent('{##main##}', '</p>');

                $page->addContent('{##main##}', '<table><tbody>'
                        . '<tr><th><p><span>Who?</span></p></th><th><p><span>What?</span></p></th></tr>'
                        . '<tr><td><p><span>Leader - Evo (evocv.6892)</span></p></td><td><p><span>General stuff and philosophical questions</span></p></td></tr>'
                        . '<tr><td><p><span>Leader - Ani (Ani.8473)</span></p></td><td><p><span>General organisation and RAIDs</span></p></td></tr>'
                        . '<tr><td><p><span>Officer - Kara (karachristina.3670)</span></p></td><td><p><span>General PvE</span></p></td></tr>'
                        . '<tr><td><p><span>Officer - Kakir (Muhahalol.2169)</span></p></td><td><p><span>GUILD HALL upgrades</span></p></td></tr>'
                        . '<tr><td><p><span>Officer - Arilin (Arilin.8716)</span></p></td><td><p><span>PVP (including pvp missions)</span></p></td></tr>'
                        . '<tr><td><p><span>Chieftain - Blessy (BlessurisHammer.9602)</span></p></td><td><p><span>HP RUNS in HoT maps</span></p></td></tr>'
                        . '</tbody></table>');

                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Of course you can always ask anyone, but this people should be your first choice if you have a specific questions to their areas.');
                $page->addContent('{##main##}', '</p>');

                $page->addContent('{##main##}', '<h3>Roster cleaning</h3>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Earlier we had a strategy to never kick an EoL guildie, no matter how long it didnt show up. But as we grow, the member count raised as well and the numbers of inactive people started to be higher than the one of the active guild members. That sometimes gave a wrong impression to new people about the guild’s activity. Then GuildWars2 became free to play. New players flooded the game, joined guilds and as we like newbies some of them joined ours. Especially those new free-to-play players stopped playing and never showed up again. That was the point when we decided we need to handle it.');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Now we let a member go when it didn’t log in for half a year. Not a single sign-on. If you didn’t make it to member yet and are still a recruit it’s a month (especially if you just started in EoL and then be away for that long time noone might remember you). We still don’t like kicking members, but it’s necessary. Every ‘cleaned’ guildie gets a mail stating that if he returns we will be welcomed back to EoL as well, all it needs is a ping :)');
                $page->addContent('{##main##}', '</p>');
                break;
            case "7dtd" :
                $page->setContent('{##main##}', '<h2>7dtd</h2>');

                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Just for Fun Server for EoL Members.');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', 'Ask Evo for the Password :)');
                $page->addContent('{##main##}', '</p>');
                break;
            case "imprint" :
            case "impressum" :
                $page->setContent('{##main##}', '<h2>Impressum</h2>');
                $page->addContent('{##main##}', '<p>Christian Voigt<br />');
                $page->addContent('{##main##}', 'Swebenhöhe, 75c<br />22159 Hamburg</p>');
                $page->addContent('{##main##}', '<p>Telefon: 0176 626 283 26<br />');
                $page->addContent('{##main##}', 'E-Mail: <a href="mailto:mail@notjustfor.me">mail@notjustfor.me</a><br />');
                $page->addContent('{##main##}', '</p>');
                $page->addContent('{##main##}', '<br /><br /><h2>Disclaimer – rechtliche Hinweise</h2>');
                $page->addContent('{##main##}', '§ 1 Warnhinweis zu Inhalten<br />');
                $page->addContent('{##main##}', 'Die kostenlosen und frei zugänglichen Inhalte dieser Webseite wurden mit größtmöglicher Sorgfalt erstellt. Der Anbieter dieser Webseite übernimmt jedoch keine Gewähr für die Richtigkeit und Aktualität der bereitgestellten kostenlosen und frei zugänglichen journalistischen Ratgeber und Nachrichten. Namentlich gekennzeichnete Beiträge geben die Meinung des jeweiligen Autors und nicht immer die Meinung des Anbieters wieder. Allein durch den Aufruf der kostenlosen und frei zugänglichen Inhalte kommt keinerlei Vertragsverhältnis zwischen dem Nutzer und dem Anbieter zustande, insoweit fehlt es am Rechtsbindungswillen des Anbieters.<br />');
                $page->addContent('{##main##}', '<br />');
                $page->addContent('{##main##}', '§ 2 Externe Links<br />');
                $page->addContent('{##main##}', 'Diese Website enthält Verknüpfungen zu Websites Dritter ("externe Links"). Diese Websites unterliegen der Haftung der jeweiligen Betreiber. Der Anbieter hat bei der erstmaligen Verknüpfung der externen Links die fremden Inhalte daraufhin überprüft, ob etwaige Rechtsverstöße bestehen. Zu dem Zeitpunkt waren keine Rechtsverstöße ersichtlich. Der Anbieter hat keinerlei Einfluss auf die aktuelle und zukünftige Gestaltung und auf die Inhalte der verknüpften Seiten. Das Setzen von externen Links bedeutet nicht, dass sich der Anbieter die hinter dem Verweis oder Link liegenden Inhalte zu Eigen macht. Eine ständige Kontrolle der externen Links ist für den Anbieter ohne konkrete Hinweise auf Rechtsverstöße nicht zumutbar. Bei Kenntnis von Rechtsverstößen werden jedoch derartige externe Links unverzüglich gelöscht.<br />');
                $page->addContent('{##main##}', '<br />');
                $page->addContent('{##main##}', '§ 3 Urheber- und Leistungsschutzrechte<br />');
                $page->addContent('{##main##}', 'Die auf dieser Website veröffentlichten Inhalte unterliegen dem deutschen Urheber- und Leistungsschutzrecht. Jede vom deutschen Urheber- und Leistungsschutzrecht nicht zugelassene Verwertung bedarf der vorherigen schriftlichen Zustimmung des Anbieters oder jeweiligen Rechteinhabers. Dies gilt insbesondere für Vervielfältigung, Bearbeitung, Übersetzung, Einspeicherung, Verarbeitung bzw. Wiedergabe von Inhalten in Datenbanken oder anderen elektronischen Medien und Systemen. Inhalte und Rechte Dritter sind dabei als solche gekennzeichnet. Die unerlaubte Vervielfältigung oder Weitergabe einzelner Inhalte oder kompletter Seiten ist nicht gestattet und strafbar. Lediglich die Herstellung von Kopien und Downloads für den persönlichen, privaten und nicht kommerziellen Gebrauch ist erlaubt.<br />');
                $page->addContent('{##main##}', '<br />');
                $page->addContent('{##main##}', 'Die Darstellung dieser Website in fremden Frames ist nur mit schriftlicher Erlaubnis zulässig.<br />');
                $page->addContent('{##main##}', '<br />');
                $page->addContent('{##main##}', '§ 4 Besondere Nutzungsbedingungen<br />');
                $page->addContent('{##main##}', 'Soweit besondere Bedingungen für einzelne Nutzungen dieser Website von den vorgenannten Paragraphen abweichen, wird an entsprechender Stelle ausdrücklich darauf hingewiesen. In diesem Falle gelten im jeweiligen Einzelfall die besonderen Nutzungsbedingungen.<p>Quelle: <a href="http://www.juraforum.de/impressum-generator/">Impressum Muster</a> von der <a href="http://www.juraforum.de/rechtsanwalt/anwalt-hamburg/">Anwaltssuche von Juraforum.de</a> in Ihrer Nähe.</p>');
                break;
            case "privacy" :
            case "datenschutz" :
                $page->setContent('{##main##}', '<h2>Datenschutzerklärung</h2>');
                $page->addContent('{##main##}', '<p><strong>Datenschutz</strong><br />Nachfolgend möchten wir Sie über unsere Datenschutzerklärung informieren. Sie finden hier Informationen über die Erhebung und Verwendung persönlicher Daten bei der Nutzung unserer Webseite. Wir beachten dabei das für Deutschland geltende Datenschutzrecht. Sie können diese Erklärung jederzeit auf unserer Webseite abrufen. ');
                $page->addContent('{##main##}', '<br /><br />');
                $page->addContent('{##main##}', 'Wir weisen ausdrücklich darauf hin, dass die Datenübertragung im Internet (z.B. bei der Kommunikation per E-Mail) Sicherheitslücken aufweisen und nicht lückenlos vor dem Zugriff durch Dritte geschützt werden kann. ');
                $page->addContent('{##main##}', '<br /><br />');
                $page->addContent('{##main##}', 'Die Verwendung der Kontaktdaten unseres Impressums zur gewerblichen Werbung ist ausdrücklich nicht erwünscht, es sei denn wir hatten zuvor unsere schriftliche Einwilligung erteilt oder es besteht bereits eine Geschäftsbeziehung. Der Anbieter und alle auf dieser Website genannten Personen widersprechen hiermit jeder kommerziellen Verwendung und Weitergabe ihrer Daten.');
                $page->addContent('{##main##}', '<br /><br />');
                $page->addContent('{##main##}', '<strong>Personenbezogene Daten</strong>');
                $page->addContent('{##main##}', '<br />');
                $page->addContent('{##main##}', 'Sie können unsere Webseite ohne Angabe personenbezogener Daten besuchen. Soweit auf unseren Seiten personenbezogene Daten (wie Name, Anschrift oder E-Mail Adresse) erhoben werden, erfolgt dies, soweit möglich, auf freiwilliger Basis. Diese Daten werden ohne Ihre ausdrückliche Zustimmung nicht an Dritte weitergegeben. Sofern zwischen Ihnen und uns ein Vertragsverhältnis begründet, inhaltlich ausgestaltet oder geändert werden soll oder Sie an uns eine Anfrage stellen, erheben und verwenden wir personenbezogene Daten von Ihnen, soweit dies zu diesen Zwecken erforderlich ist (Bestandsdaten). Wir erheben, verarbeiten und nutzen personenbezogene Daten soweit dies erforderlich ist, um Ihnen die Inanspruchnahme des Webangebots zu ermöglichen (Nutzungsdaten). Sämtliche personenbezogenen Daten werden nur solange gespeichert wie dies für den genannten Zweck (Bearbeitung Ihrer Anfrage oder Abwicklung eines Vertrags) erforderlich ist. Hierbei werden steuer- und handelsrechtliche Aufbewahrungsfristen berücksichtigt. Auf Anordnung der zuständigen Stellen dürfen wir im Einzelfall Auskunft über diese Daten (Bestandsdaten) erteilen, soweit dies für Zwecke der Strafverfolgung, zur Gefahrenabwehr, zur Erfüllung der gesetzlichen Aufgaben der Verfassungsschutzbehörden oder des Militärischen Abschirmdienstes oder zur Durchsetzung der Rechte am geistigen Eigentum erforderlich ist.</p><p><strong>Kommentarfunktionen</strong><br />');
                $page->addContent('{##main##}', 'Im Rahmen der Kommentarfunktion erheben wir personenbezogene Daten (z.B. Name, E-Mail) im Rahmen Ihrer Kommentierung zu einem Beitrag nur in dem Umfang wie Sie ihn uns mitgeteilt haben. Bei der Veröffentlichung eines Kommentars wird die von Ihnen angegebene Email-Adresse gespeichert, aber nicht veröffentlicht. Ihr Name wird veröffentlicht, wenn Sie nicht unter Pseudonym geschrieben haben.</p><p><strong>Auskunftsrecht</strong><br />Sie haben das jederzeitige Recht, sich unentgeltlich und unverzüglich über die zu Ihrer Person erhobenen Daten zu erkundigen. Sie haben das jederzeitige Recht, Ihre Zustimmung zur Verwendung Ihrer angegeben persönlichen Daten mit Wirkung für die Zukunft zu widerrufen. Zur Auskunftserteilung wenden Sie sich bitte an den Anbieter unter den Kontaktdaten im Impressum.</p><p>Quelle: <a href="http://www.juraforum.de">www.juraforum.de</a></p>');
                break;
        }
        
        
    }

}

$pages = new Pages();
$pages->initEnv();
