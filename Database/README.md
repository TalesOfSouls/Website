# Database

The website uses `TalesOfSouls.db` (SQLite, see `config.php`).

## Scripts

| Script | Purpose |
| --- | --- |
| `php Database/migrate.php [db]` | Adds the tables and columns the website needs. Can be run any number of times, existing tables/columns are not touched. |
| `php Database/seed.php [db]` | Runs the migration and fills the database with deterministic dummy data. Running it again replaces the dummy data. |
| `skills.sql` | The skill list. It is imported by the migration if the `skill` table has no `skill_name` column yet. |

## Dummy data

- Dummy rows use ids `>= 1001` in the existing game tables (`account`, `character`, `guild`, ...).
  The website-only tables (`encounter*`, `season*`, `feedback*`, `character_event`, ...) are cleared completely by the seed script.
- All dummy accounts use the password `password` (e.g. sign in as `Aldric`, `Mirelle` or the developer `Morgwen`).
- Existing data (game data, accounts 1 and 2, the reserved guild names, ...) is not removed. `spl1nes` and `admin` are made administrators.

## Login accounts

| Account | Password | Role |
| --- | --- | --- |
| `admin` | `AdminPass2026!` | Administrator (`/admin`: news, wiki, FAQ, development, feedback moderation, accounts, messages) |
| `tester` | `TestUser2026!` | Player (feedback, votes, comments, account settings) |
| `Morgwen` | `password` | Developer (can moderate feedback) |

Roles (`account_role`): 0 = player, 1 = developer, 2 = admin. **Change these passwords before the site goes public**, the database is part of the repository.

## Tables added for the website

| Table | Content |
| --- | --- |
| `character_event` | Event log of a character (level up, dungeon clear, loot, ...) |
| `character_level_log` | Level ups incl. the playtime at that moment |
| `instance`, `instance_boss`, `pvp_mode` | Dungeons, raids, world bosses, PvP maps/modes |
| `encounter`, `encounter_member`, `encounter_member_skill`, `encounter_boss`, `encounter_attempt`, `encounter_boss_stat`, `encounter_score` | Dungeon runs, raids, world boss fights and PvP matches incl. statistics |
| `achievement_category`, `account_achievement_tier` | Achievement categories and unlocked achievement tiers |
| `season`, `season_boss`, `season_progress`, `season_boss_kill` | Seasons (world dungeon, main bosses, ladder) and the seasonal results of the accounts |
| `feedback`, `feedback_state_log`, `feedback_vote`, `feedback_comment`, `feedback_comment_vote` | Feedback board |
| `contact_message` | Messages of the contact form |
| `news` | News posts (markdown, draft/published, publish date). Imported once from `Application/Frontend/data/news` |
| `wiki_page` | Wiki articles (markdown, optional data module, navigation order). Imported once from `wiki_pages.json` |
| `milestone`, `development_area` | Roadmap milestones and development progress of the development page |

New columns: `account` (created, lastlogin, status, role), `character` (race, created, season, hardcore, dead, item_level),
`guild` (description, created, recruiting), `guild_member` (guild, joined), `item_individual` (rolled items),
`character_equipment.artifact`, `skill` (cooldown, kind, target).

Units: playtime in seconds, durations in seconds, the level is calculated from the xp (`player_level`).
