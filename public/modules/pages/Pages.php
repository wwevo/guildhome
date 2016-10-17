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

    private $registered_pages = ['home', 'about', '7dtd'];

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
        }
    }

}

$pages = new Pages();
$pages->initEnv();
