-- MAIN DATABASE OF THE PORTFOLIO APPLICATION

-- Drop the database if it exists and create a new one
DROP DATABASE IF EXISTS `portfolio`;
-- Create the database with UTF8MB4 character set and collation
CREATE DATABASE IF NOT EXISTS `portfolio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Use the database
USE `portfolio`;


-- PROJECT TABLES
CREATE TABLE `TechnoProject`(
    `code` VARCHAR(20) NOT NULL,
    `libelle` VARCHAR(35) NOT NULL,
    `color` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`code`)
) ENGINE = InnoDB;

CREATE TABLE `Project`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(70) NOT NULL,
    `description` TEXT NOT NULL,
    `link` VARCHAR(255) NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE `ImageProject`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `project_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`project_id`) REFERENCES `Project`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `HaveProject`(
    `project_id` INT NOT NULL,
    `techno_code` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`project_id`, `techno_code`),
    FOREIGN KEY (`project_id`) REFERENCES `Project`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`techno_code`) REFERENCES `TechnoProject`(`code`) ON DELETE CASCADE
) ENGINE = InnoDB;




-- DIPLOMA TABLES
CREATE TABLE `Diploma`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `school` VARCHAR(100) NOT NULL,
    `country` VARCHAR(100) NOT NULL,
    `start_date` VARCHAR(4) NOT NULL,
    `end_date` VARCHAR(4) NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- INSERT INITIAL DATA FROM TECHNOLOGIES
INSERT INTO `TechnoProject` (`code`, `libelle`, `color`) VALUES
('ANDROID', 'Android', '#3ddc84'),
('ANDROIDSTUDIO', 'Android Studio', '#3ddc84'),
('ANGULAR', 'Angular', '#dd0031'),
('ANSIBLE', 'Ansible', '#ee0000'),
('APPCODE', 'AppCode', '#888888'),
('ASSEMBLY', 'Assembly', '#808080'),
('ATOM', 'Atom', '#66595c'),
('BASH', 'Bash', '#4eaa25'),
('BASIC', 'BASIC', '#ff4500'),
('BITBUCKET', 'Bitbucket', '#0052cc'),
('BLENDER', 'Blender', '#f5792a'),
('BOOTSTRAP', 'Bootstrap', '#563d7c'),
('BRACKETS', 'Brackets', '#3389de'),
('C', 'C', '#a8b9cc'),
('CHEF', 'Chef', '#f14f2e'),
('CLION', 'CLion', '#00599c'),
('CLOJURE', 'Clojure', '#5881d8'),
('COBOL', 'COBOL', '#005f9e'),
('CONFLUENCE', 'Confluence', '#172b4d'),
('CPP', 'C++', '#00599c'),
('CSHARP', 'C#', '#68217a'),
('CSS', 'CSS', '#264de4'),
('DART', 'Dart', '#0175c2'),
('DATAGRIP', 'DataGrip', '#512bd4'),
('DOCKER', 'Docker', '#2496ed'),
('DOTNET', '.NET', '#512bd4'),
('ECLIPSE', 'Eclipse', '#2c2255'),
('ELIXIR', 'Elixir', '#6e4a7e'),
('ELK', 'ELK Stack', '#005571'),
('EMACS', 'Emacs', '#7f5ab6'),
('ERLANG', 'Erlang', '#a90533'),
('EXPRESS', 'Express.js', '#404040'),
('FIGMA', 'Figma', '#0acf83'),
('FILEZILLA', 'FileZilla', '#bf0000'),
('FIREBASE', 'Firebase', '#ffca28'),
('FLUTTER', 'Flutter', '#02569b'),
('FORTRAN', 'Fortran', '#4d41b1'),
('GIMP', 'GIMP', '#5c5543'),
('GIT', 'Git', '#f34f29'),
('GITHUB', 'GitHub', '#181717'),
('GITLAB', 'GitLab', '#fc6d26'),
('GO', 'Go', '#00add8'),
('GOLAND', 'GoLand', '#00add8'),
('GRAFANA', 'Grafana', '#f46800'),
('HASKELL', 'Haskell', '#5e5086'),
('HEROKU', 'Heroku', '#6762a6'),
('HTML', 'HTML', '#e34c26'),
('ILLUSTRATOR', 'Illustrator', '#ff7f00'),
('INKSCAPE', 'Inkscape', '#000000'),
('INTELLIJ', 'IntelliJ IDEA', '#000080'),
('IOS', 'iOS', '#888888'),
('JAVA', 'Java', '#007396'),
('JENKINS', 'Jenkins', '#d24939'),
('JIRA', 'Jira', '#0052cc'),
('JQUERY', 'jQuery', '#0868ac'),
('JS', 'JavaScript', '#f0db4f'),
('KOTLIN', 'Kotlin', '#0095d5'),
('KUBERNETES', 'Kubernetes', '#326ce5'),
('LARAVEL', 'Laravel', '#ff2d20'),
('LINUX', 'Linux', '#f5a442'),
('LUA', 'Lua', '#000080'),
('MACOS', 'macOS', '#6e6e6e'),
('MONGO', 'MongoDB', '#4db33d'),
('MYSQL', 'MySQL', '#4479a1'),
('NAGIOS', 'Nagios', '#cc0000'),
('NETBEANS', 'NetBeans', '#1b6ac6'),
('NODE', 'Node.js', '#68a063'),
('OBJECTIVEC', 'Objective-C', '#686868'),
('PERL', 'Perl', '#39457e'),
('PHOTOSHOP', 'Photoshop', '#31a8ff'),
('PHP', 'PHP', '#8892be'),
('PHPSTORM', 'PhpStorm', '#8892be'),
('POSTGRESQL', 'PostgreSQL', '#336791'),
('POWERSHELL', 'PowerShell', '#012456'),
('PROMETHEUS', 'Prometheus', '#e6522c'),
('PUPPET', 'Puppet', '#302b6d'),
('PUTTY', 'PuTTY', '#000080'),
('PYCHARM', 'PyCharm', '#4caf50'),
('PYTHON', 'Python', '#306998'),
('R', 'R', '#276dc3'),
('RAILS', 'Ruby on Rails', '#cc0000'),
('REACT', 'React', '#61dafb'),
('RIDER', 'Rider', '#68217a'),
('RUBY', 'Ruby', '#cc342d'),
('RUBYMINE', 'RubyMine', '#cc342d'),
('RUST', 'Rust', '#dea584'),
('SALTSTACK', 'SaltStack', '#d79a23'),
('SASS', 'Sass', '#cc6699'),
('SCALA', 'Scala', '#c22d40'),
('SHELL', 'Shell', '#89e051'),
('SOCKETIO', 'Socket.IO', '#010101'),
('SQL', 'SQL', '#4479a1'),
('SQLITE', 'SQLite', '#003b57'),
('SSH', 'SSH', '#d0d0d0'),
('SUBLIME', 'Sublime Text', '#ff9800'),
('SWIFT', 'Swift', '#ffac45'),
('SYMFONY', 'Symfony', '#111111'),
('TERRAFORM', 'Terraform', '#623ce4'),
('TRELLO', 'Trello', '#0079bf'),
('TYPESCRIPT', 'TypeScript', '#007acc'),
('UNITY', 'Unity', '#000000'),
('VIM', 'Vim', '#019733'),
('VIRTUALBOX', 'VirtualBox', '#183a61'),
('VISUALSTUDIO', 'Visual Studio', '#68217a'),
('VMWARE', 'VMware', '#607078'),
('VSCODE', 'Visual Studio Code', '#0078d7'),
('VUE', 'Vue.js', '#42b883'),
('WEBSTORM', 'WebStorm', '#000080'),
('WINDOWS', 'Windows', '#0078d7'),
('WINSCP', 'WinSCP', '#4e9a06'),
('WORDPRESS', 'WordPress', '#21759b'),
('XCODE', 'Xcode', '#147efb'),
('XD', 'Adobe XD', '#ff61f6'),
('ZABBIX', 'Zabbix', '#ff0000');