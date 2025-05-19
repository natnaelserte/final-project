<?php
// language.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Available Languages ---
$available_langs = ['en' => 'English', 'am' => 'አማርኛ'];
$default_lang = 'en';

// --- Set Language ---
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
    $current_page = basename($_SERVER['PHP_SELF']);
    if (!empty($_SERVER['QUERY_STRING'])) {
        $query = $_SERVER['QUERY_STRING'];
        parse_str($query, $params);
        unset($params['lang']); // Remove existing lang param
        $query = http_build_query($params); // Rebuild query string
        if (!empty($query)) {
            $current_page .= '?' . $query;
        }
    }
    header("Location: " . $current_page);
    exit();
}

// --- Get Current Language ---
$current_lang = isset($_SESSION['lang']) && array_key_exists($_SESSION['lang'], $available_langs)
                ? $_SESSION['lang']
                : $default_lang;

// --- Translations Array ---
$translations = [
    // --- Navbar Translations ---
    'home' => ['en' => 'Home', 'am' => 'መነሻ'],
    'candidates' => ['en' => 'Candidates', 'am' => 'እጩዎች'],
    'about' => ['en' => 'About', 'am' => 'ስለ እኛ'],
    'live' => ['en' => 'Live', 'am' => 'ቀጥታ ስርጭት'],
    'register' => ['en' => 'Register', 'am' => 'ይመዝገቡ'],
    'login' => ['en' => 'Login', 'am' => 'ይግቡ'],
    'faq' => ['en' => 'FAQ', 'am' => 'ተደጋጋሚ ጥያቄዎች'],
    'announcements' => ['en' => 'Announcements', 'am' => 'ማስታወቂያዎች'],
    'website_title' => ['en' => 'Community Voting', 'am' => 'የማህበረሰብ ድምጽ መስጫ'],

    // --- Landing Page Translations ---
    'voting_event_ended' => ['en' => "The voting event '<strong>%s</strong>' has ended.", 'am' => "የድምጽ አሰጣጥ ዝግጅት '<strong>%s</strong>' ተጠናቋል።"],
    'ongoing_vote' => ['en' => "Ongoing Vote: <strong>%s</strong> (Ends at %s)", 'am' => "በሂደት ላይ ያለ ድምጽ: <strong>%s</strong> (በ %s ይጠናቀቃል)"],
    'no_active_event' => ['en' => "No active voting event at this time.", 'am' => "በአሁኑ ሰዓት ንቁ የድምጽ አሰጣጥ ዝግጅት የለም።"],
    'error_retrieve_event' => ['en' => 'Error: Could not retrieve voting event information.', 'am' => 'ስህተት፦ የድምጽ አሰጣጥ ዝግጅት መረጃ ማግኘት አልተቻለም።'],
    'countdown_ends_in' => ['en' => "Ends in: ", 'am' => "የሚጠናቀቀው በ: "],
    'countdown_ended' => ['en' => "Voting Period Ended", 'am' => "የድምጽ መስጫ ጊዜው አልቋል"],
    'hero_sub_headline' => ['en' => 'Arbaminch Community Voting Services', 'am' => 'የአርባምንጭ ማህበረሰብ ድምጽ መስጫ አገልግሎቶች'],
    'hero_main_headline' => ['en' => 'Your Voice, Our Future', 'am' => 'የእርስዎ ድምጽ፣ የኛ ነገ'],
    'vote_now' => ['en' => 'Vote Now', 'am' => 'አሁን ድምጽ ይስጡ'],
    'learn_more' => ['en' => 'Learn More', 'am' => 'የበለጠ ይወቁ'],
    'service_governance_title' => ['en' => 'Community Governance', 'am' => 'የማህበረሰብ አስተዳደር'],
    'service_governance_desc' => ['en' => 'Participate in decisions shaping our local governance.', 'am' => 'የአካባቢያችንን አስተዳደር በሚቀርፁ ውሳኔዎች ላይ ይሳተፉ።'],
    'service_elections_title' => ['en' => 'Secure Elections', 'am' => 'ደህንነቱ የተጠበቀ ምርጫ'],
    'service_elections_desc' => ['en' => 'Transparent and secure voting for all community matters.', 'am' => 'ለሁሉም የማህበረሰብ ጉዳዮች ግልጽ እና ደህንነቱ የተጠበቀ ድምጽ መስጠት።'],
    'service_engagement_title' => ['en' => 'Civic Engagement', 'am' => 'የዜጎች ተሳትፎ'],
    'service_engagement_desc' => ['en' => 'Empowering every member to contribute to community development.', 'am' => 'እያንዳንዱ አባል በማህበረሰብ ልማት ላይ አስተዋፅኦ እንዲያበረክት ማብቃት።'],
    'service_privacy_title' => ['en' => 'Data Privacy', 'am' => 'የመረጃ ግላዊነት'],
    'service_privacy_desc' => ['en' => 'Your participation and vote are handled with utmost confidentiality.', 'am' => 'የእርስዎ ተሳትፎ እና ድምጽ በከፍተኛ ሚስጥራዊነት ይያዛሉ።'],
    'service_voter_info_title' => ['en' => 'Voter Information', 'am' => 'የመራጮች መረጃ'],
    'service_voter_info_desc' => ['en' => 'Access clear information about candidates and proposals.', 'am' => 'ስለ እጩዎች እና ሀሳቦች ግልጽ መረጃ ያግኙ።'],
    'service_results_title' => ['en' => 'Verified Results', 'am' => 'የተረጋገጡ ውጤቶች'],
    'service_results_desc' => ['en' => 'Timely and transparent announcement of voting outcomes.', 'am' => 'ወቅታዊ እና ግልጽ የድምጽ አሰጣጥ ውጤቶች ማስታወቂያ።'],
    'go_to_top' => ['en' => 'Go to top', 'am' => 'ወደ ላይ ይሂዱ'],

    // --- ABOUT US PAGE TRANSLATIONS ---
    'about_page_title' => ['en' => 'About Us - AMU Voting System', 'am' => 'ስለ እኛ - የአርባምንጭ ዩኒቨርስቲ የድምፅ መስጫ ስርዓት'],
    'about_hero_title' => ['en' => 'Empowering Student Voice Through Secure Online Voting', 'am' => 'ደህንነቱ በተጠበቀ የመስመር ላይ ድምጽ አሰጣጥ የተማሪ ድምጽን ማጎልበት'],
    'about_hero_subtitle' => ['en' => 'Building a transparent, accessible, and modern election process for Arbaminch University.', 'am' => 'ለአርባምንጭ ዩኒቨርሲቲ ግልጽ፣ ተደራሽ እና ዘመናዊ የምርጫ ሂደት መገንባት።'],
    'about_mission_title' => ['en' => 'Our Mission', 'am' => 'የእኛ ተልዕኮ'],
    'about_mission_p1' => ['en' => 'The Arbaminch University Online Voting System is a secure and efficient platform designed to modernize the election process within our university. This system replaces traditional paper-based voting with a user-friendly online interface, making voting more accessible, convenient, and transparent for all students and faculty.', 'am' => 'የአርባምንጭ ዩኒቨርሲቲ የመስመር ላይ የድምፅ መስጫ ስርዓት በዩኒቨርሲቲያችን ውስጥ የምርጫ ሂደቱን ዘመናዊ ለማድረግ የተነደፈ አስተማማኝ እና ቀልጣፋ መድረክ ነው። ይህ ስርዓት ባህላዊ የወረቀት ድምጽ አሰጣጥን ለሁሉም ተማሪዎች እና መምህራን በቀላሉ ተደራሽ፣ ምቹ እና ግልጽ በሆነ የተጠቃሚ-ተስማሚ የመስመር ላይ በይነገጽ ይተካል።'],
    'about_mission_p2_subtitle' => ['en' => 'We are committed to a fair and inclusive electoral process.', 'am' => 'ለፍትሃዊ እና ሁሉን አቀፍ የምርጫ ሂደት ቁርጠኞች ነን።'],
    'about_mission_p3' => ['en' => 'Our primary goal is to enhance participation in university elections by providing a seamless and reliable voting experience. The system incorporates robust security measures to ensure the integrity of the voting process and protect against fraud.', 'am' => 'ዋናው ግባችን እንከን የለሽ እና አስተማማኝ የድምፅ አሰጣጥ ተሞክሮ በማቅረብ በዩኒቨርሲቲ ምርጫዎች ላይ ተሳትፎን ማሳደግ ነው። ስርዓቱ የድምፅ አሰጣጥ ሂደቱን ንፅህና ለማረጋገጥ እና ከማጭበርበር ለመከላከል ጠንካራ የደህንነት እርምጃዎችን ያካትታል።'],
    'mission_item1' => ['en' => 'Secure Voter Authentication', 'am' => 'ደህንነቱ የተጠበቀ የመራጮች ማረጋገጫ'],
    'mission_item2' => ['en' => 'Encrypted Voting Data', 'am' => 'የተመሰጠረ የድምፅ መስጫ መረጃ'],
    'mission_item3' => ['en' => 'Real-time Audit Trails & Transparency', 'am' => 'የእውነተኛ ጊዜ የኦዲት ፍኖት እና ግልጽነት'],
    'mission_item4' => ['en' => 'Accessible and User-Friendly Interface', 'am' => 'ተደራሽ እና ለተጠቃሚ ምቹ የሆነ በይነገጽ'],
    'alt_university_collaboration' => ['en' => 'University Collaboration', 'am' => 'የዩኒቨርሲቲ ትብብር'],
    'about_story_title' => ['en' => 'Our Story', 'am' => 'የእኛ ታሪክ'],
    'about_story_p1' => ['en' => 'The vision for a modern online voting system at Arbaminch University was born from a desire to increase student engagement and streamline administrative processes. Recognizing the potential of technology to transform traditional methods, a dedicated team of faculty and IT professionals embarked on this project.', 'am' => 'በአርባምንጭ ዩኒቨርሲቲ የዘመናዊ የመስመር ላይ የድምፅ መስጫ ስርዓት ራዕይ የተወለደው የተማሪዎችን ተሳትፎ ለማሳደግ እና የአስተዳደር ሂደቶችን ለማቀላጠፍ ካለው ፍላጎት ነው። የቴክኖሎጂን አቅም ባህላዊ ዘዴዎችን ለመለወጥ በመገንዘብ፣ ቁርጠኛ የሆነ የመምህራን እና የአይቲ ባለሙያዎች ቡድን ይህንን ፕሮጀክት ጀመረ።'],
    'about_story_p2' => ['en' => 'From initial concept through development and testing, our focus has been on creating a platform that is not only technologically advanced but also trustworthy and easy to use for every member of the AMU community. We aim to set a new standard for university elections.', 'am' => 'ከመጀመሪያው ፅንሰ-ሀሳብ እስከ ልማት እና ሙከራ ድረስ፣ ትኩረታችን በቴክኖሎጂ የላቀ ብቻ ሳይሆን ለእያንዳንዱ የአርባምንጭ ዩኒቨርስቲ ማህበረሰብ አባል ታማኝ እና ለአጠቃቀም ቀላል የሆነ መድረክ መፍጠር ላይ ነው። ለዩኒቨርሲቲ ምርጫዎች አዲስ መስፈርት ለማውጣት ዓላማ አለን።'],
    'learn_more_amu' => ['en' => 'Learn more about AMU', 'am' => 'ስለ አርባምንጭ ዩኒቨርስቲ የበለጠ ይወቁ'],
    'alt_team_discussion' => ['en' => 'Team Discussion', 'am' => 'የቡድን ውይይት'],
    'about_team_title' => ['en' => 'Our Team / Project Leads', 'am' => 'የእኛ ቡድን / የፕሮጀክት መሪዎች'],
    'leader_name_natnael' => ['en' => 'Natnael Serte', 'am' => 'ናትናኤል ሰርጸ'],
    'leader_name_absir' => ['en' => 'Absir Mugeta', 'am' => 'አብሲር ሙጌታ'],
    'leader_name_mintesnot' => ['en' => 'Mintesnot Gulilat', 'am' => 'ምንትስኖት ጉሊላት'],
    'leader_name_eman' => ['en' => 'Eman Seid', 'am' => 'ኢማን ሰይድ'],
    'leader_name_helina' => ['en' => 'Helina Tensay', 'am' => 'ሄሊና ተንሳይ'],
    'linkedin_profile_of' => ['en' => 'LinkedIn Profile of', 'am' => 'የሊንክድኢን ገጽ የ'],
    'telegram_profile_of' => ['en' => 'Telegram Profile of', 'am' => 'የቴሌግራም ገጽ የ'],
    'about_testimonials_title' => ['en' => 'What Our University Community Says', 'am' => 'የዩኒቨርሲቲያችን ማህበረሰብ ምን ይላል'],
    'alt_amu_logo' => ['en' => 'AMU Logo', 'am' => 'የአርባምንጭ ዩኒቨርስቲ አርማ'],
    'testimonial1_text' => ['en' => '"The new online voting system is a fantastic step forward for AMU. It\'s intuitive, easy to use, and I feel confident that my vote is secure. This will definitely increase participation!"', 'am' => '"አዲሱ የመስመር ላይ የድምፅ መስጫ ስርዓት ለአርባምንጭ ዩኒቨርስቲ ትልቅ እርምጃ ነው። ለመረዳት ቀላል፣ ለአጠቃቀም ምቹ እና ድምጼ ደህንነቱ የተጠበቀ እንደሆነ ይሰማኛል። ይህ በእርግጠኝነት ተሳትፎን ይጨምራል!"'],
    'testimonial1_author_name' => ['en' => 'Student Tesfaye G.', 'am' => 'ተማሪ ተስፋዬ ገ.'],
    'testimonial1_author_title' => ['en' => '3rd Year, Computer Science', 'am' => '3ኛ ዓመት፣ የኮምፒውተር ሳይንስ'],
    'testimonial2_text' => ['en' => '"As a faculty member, I appreciate the efficiency and transparency this system brings to our university elections. It simplifies the process for everyone involved."', 'am' => '"እንደ መምህር፣ ይህ ስርዓት ለዩኒቨርሲቲ ምርጫዎቻችን የሚያመጣውን ቅልጥፍና እና ግልጽነት አደንቃለሁ። ለሁሉም ተሳታፊዎች ሂደቱን ያቃልላል።"'],
    'testimonial2_author_name' => ['en' => 'Students', 'am' => 'ተማሪዎች'],
    'testimonial2_author_title' => ['en' => 'Faculty of computing and software Engineering', 'am' => 'የኮምፒውቲንግ እና ሶፍትዌር ምህንድስና ፋኩልቲ'],
    'carousel_previous' => ['en' => 'Previous', 'am' => 'ቀዳሚ'],
    'carousel_next' => ['en' => 'Next', 'am' => 'ቀጣይ'],
    'cta_title' => ['en' => 'Ready to Participate?', 'am' => 'ለመሳተፍ ዝግጁ ነዎት?'],
    'cta_text' => ['en' => 'Learn more about upcoming elections, how to register, and the voting process. Your voice matters!', 'am' => 'ስለ መጪ ምርጫዎች፣ እንዴት መመዝገብ እንደሚችሉ እና የድምፅ አሰጣጥ ሂደቱን የበለጠ ይወቁ። የእርስዎ ድምጽ ዋጋ አለው!'],
    'cta_button' => ['en' => 'Access Voter\'s Portal', 'am' => 'የመራጮች መግቢያን ይድረሱ'],
    // Add any other keys from your about.php page here
];

// --- THIS IS THE ONLY DEFINITION OF t() ---
function t($key, ...$args) {
    global $translations, $current_lang, $default_lang;
    $string = '';

    if (isset($translations[$key][$current_lang])) {
        $string = $translations[$key][$current_lang];
    } elseif (isset($translations[$key][$default_lang])) {
        $string = $translations[$key][$default_lang];
    } else {
        // If a default text was passed as the first argument after the key, use it. Otherwise, use the key.
        // This check assumes that if $args[0] is a string and it's the only argument, it's the default text.
        if (isset($args[0]) && is_string($args[0]) && count($args) == 1) {
            return $args[0];
        }
        return $key; // Fallback to the key itself if no translation and no suitable default text
    }

    // If the string for the current language was found, and arguments were passed for sprintf
    // And also ensure that $args are not the 'default text' argument we might have used above.
    // This part is tricky if 'default text' can also contain sprintf placeholders.
    // For simplicity: if $string has '%' and $args exist, assume $args are for sprintf.
    if (!empty($string) && !empty($args) && strpos($string, '%') !== false) {
        // We need to make sure $args are the actual sprintf arguments,
        // not the 'default text' if it was passed.
        // If the default text was $args[0] and count($args) == 1, it was already returned.
        // So, if we reach here with $args, they are intended for sprintf.
        return vsprintf($string, $args);
    }
    return $string; // Return the plain translated string
}
// --- END OF t() DEFINITION ---

// --- THIS IS THE ONLY DEFINITION OF lang_url() ---
function lang_url($lang_code) {
    $current_page = basename($_SERVER['PHP_SELF']);
    $query_params = $_GET;
    $query_params['lang'] = $lang_code;
    return $current_page . '?' . http_build_query($query_params);
}
// --- END OF lang_url() DEFINITION ---

?>