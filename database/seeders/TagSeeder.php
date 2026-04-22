<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            '16+', '18+', '19+', '3D Hentai', '3P', 'Action', 'Adult', 'Adventure', 'Ahegao', 'Anal',
            'Angel', 'Ảnh động', 'Animal', 'Animal girl', 'Áo Dài', 'Apron', 'Armpit', 'Artist CG', 'Based Game', 'BBM',
            'BBW', 'BDSM', 'Bestiality', 'Big Ass', 'Big Boobs', 'Big Penis', 'Blackmail', 'Bloomers', 'BlowJobs', 'Body Swap',
            'Bodysuit', 'Bondage', 'Breast Sucking', 'BreastJobs', 'Brocon', 'Brother', 'Business Suit', 'Catgirls', 'Che ít', 'Che nhiều',
            'Cheating', 'Chikan', 'Chinese Dress', 'Có che', 'Comedy', 'Comic', 'Con gái', 'Condom', 'Công Sở', 'Cosplay',
            'Cousin', 'Crotch Tattoo', 'Cunnilingus', 'Dark Skin', 'Daughter', 'Deepthroat', 'Demon', 'DemonGirl', 'Devil', 'DevilGirl',
            'Dirty', 'Dirty Old Man', 'DogGirl', 'Double Penetration', 'Doujinshi', 'Drama', 'Drug', 'Đã full', 'Ecchi', 'Echi',
            'Elder Sister', 'Elf', 'Exhibitionism', 'Fantasy', 'Father', 'Femdom', 'Fingering', 'Footjob', 'Foxgirls', 'Full Color',
            'Furry', 'Futanari', 'GangBang', 'Garter Belts', 'Gender Bender', 'Ghost', 'Glasses', 'Gothic Lolita', 'Group', 'Guro',
            'Hài hước', 'Hairy', 'Handjob', 'Harem', 'Hentai', 'HentaiVN', 'Hiện đại', 'Historical', 'Horror', 'Housewife',
            'Humiliation', 'Idol', 'Imouto', 'Incest', 'Insect (Côn Trùng)', 'Invisible', 'Isekai', 'Không che', 'Không NTR', 'Kimono',
            'Kissing', 'Kuudere', 'Lãng Mạn', 'Lolicon', 'Maids', 'MANGA', 'Manhua', 'Manhwa', 'Masturbation', 'Mature',
            'Miko', 'Milf', 'Mind Break', 'Mind Control', 'Mizugi', 'Monster', 'Monstergirl', 'Mother', 'Nakadashi', 'Netori',
            'Ngôn Tình', 'Non-hen', 'NTR', 'Nun', 'Nurse', 'Old Man', 'One shot', 'Oneshot', 'Oral', 'Osananajimi',
            'Paizuri', 'Pantyhose', 'Ponytail', 'Pregnant', 'Prostitution', 'Rape', 'Riêng tư', 'Rimjob', 'Romanc', 'Romance',
            'Ryona', 'Scat', 'School Uniform', 'SchoolGirl', 'Series', 'Sex Toys', 'Shimapan', 'Short Hentai', 'Shota', 'Shoujo',
            'Siscon', 'Sister', 'Slave', 'Sleeping', 'SM/BDSM/SUB-DOM', 'Small Boobs', 'Smut', 'Son', 'Sports', 'Stockings',
            'Supernatural', 'Sweating', 'Swimsuit', 'Tall Girl', 'Teacher', 'Tentacles', 'Time Stop', 'Tình Cảm', 'Tomboy', 'Tracksuit',
            'Transformation', 'Trap', 'Truyện chữ', 'Truyện Màu', 'Truyện tranh 18+', 'Truyện Việt', 'Tsundere', 'Twins', 'Twintails', 'Vampire',
            'Vanilla', 'Vếu to', 'Virgin', 'Webtoon', 'X-ray', 'Yandere', 'Yaoi', 'Yuri', 'Zombie'
        ];

        foreach ($tags as $tag) {
            DB::table('tags')->updateOrInsert(
                [
                    'name' => $tag,
                    'slug' => Str::slug($tag),
                    'is_verified' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
