-- ============================================================
-- База данных: gosha_rubchinskiy
-- Онлайн-магазин "ГОША РУБЧИНСКИЙ"
-- ============================================================

CREATE DATABASE IF NOT EXISTS gosha_rubchinskiy
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gosha_rubchinskiy;

-- Роли пользователей
CREATE TABLE IF NOT EXISTS Roles (
    RoleId   INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50)  NOT NULL
) ENGINE=InnoDB;

-- Пользователи
CREATE TABLE IF NOT EXISTS Users (
    UserId       INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    RoleId       INT          NOT NULL DEFAULT 2,
    Email        VARCHAR(100) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    FullName     VARCHAR(150),
    CreatedAt    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (RoleId) REFERENCES Roles(RoleId)
) ENGINE=InnoDB;

-- Категории
CREATE TABLE IF NOT EXISTS Categories (
    CategoryId   INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(100) NOT NULL,
    Description  TEXT
) ENGINE=InnoDB;

-- Товары
CREATE TABLE IF NOT EXISTS Products (
    ProductId   INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    CategoryId  INT            NOT NULL,
    ProductName VARCHAR(150)   NOT NULL,
    Price       DECIMAL(10,2)  NOT NULL,
    Description TEXT,
    IsActive    TINYINT(1)     NOT NULL DEFAULT 1,
    CreatedAt   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CategoryId) REFERENCES Categories(CategoryId)
) ENGINE=InnoDB;

-- Изображения товаров
CREATE TABLE IF NOT EXISTS ProductImages (
    ImageId   INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ProductId INT          NOT NULL,
    ImagePath VARCHAR(255) NOT NULL,
    IsMain    TINYINT(1)   NOT NULL DEFAULT 0,
    FOREIGN KEY (ProductId) REFERENCES Products(ProductId) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Размеры (справочник)
CREATE TABLE IF NOT EXISTS Sizes (
    SizeId   INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
    SizeName VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

-- Варианты товара по размеру
CREATE TABLE IF NOT EXISTS ProductSizes (
    ProductSizeId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ProductId     INT NOT NULL,
    SizeId        INT NOT NULL,
    Quantity      INT NOT NULL DEFAULT 0,
    FOREIGN KEY (ProductId) REFERENCES Products(ProductId) ON DELETE CASCADE,
    FOREIGN KEY (SizeId)    REFERENCES Sizes(SizeId)
) ENGINE=InnoDB;

-- Статусы заказов
CREATE TABLE IF NOT EXISTS OrderStatuses (
    StatusId   INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
    StatusName VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Заказы
CREATE TABLE IF NOT EXISTS Orders (
    OrderId     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    UserId      INT            NOT NULL,
    StatusId    INT            NOT NULL DEFAULT 1,
    OrderDate   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    TotalAmount DECIMAL(10,2)  NOT NULL,
    FOREIGN KEY (UserId)   REFERENCES Users(UserId),
    FOREIGN KEY (StatusId) REFERENCES OrderStatuses(StatusId)
) ENGINE=InnoDB;

-- Состав заказов
CREATE TABLE IF NOT EXISTS OrderItems (
    OrderItemId   INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    OrderId       INT            NOT NULL,
    ProductSizeId INT            NOT NULL,
    Quantity      INT            NOT NULL,
    Price         DECIMAL(10,2)  NOT NULL,
    FOREIGN KEY (OrderId)       REFERENCES Orders(OrderId) ON DELETE CASCADE,
    FOREIGN KEY (ProductSizeId) REFERENCES ProductSizes(ProductSizeId)
) ENGINE=InnoDB;

-- Корзина
CREATE TABLE IF NOT EXISTS CartItems (
    CartItemId    INT      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    UserId        INT      NOT NULL,
    ProductSizeId INT      NOT NULL,
    Quantity      INT      NOT NULL DEFAULT 1,
    AddedAt       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserId)        REFERENCES Users(UserId)        ON DELETE CASCADE,
    FOREIGN KEY (ProductSizeId) REFERENCES ProductSizes(ProductSizeId) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- Начальные данные
-- ============================================================

INSERT INTO Roles (RoleName) VALUES ('admin'), ('customer');

INSERT INTO OrderStatuses (StatusName) VALUES
('Новый'), ('Подтверждён'), ('В обработке'), ('Отправлен'), ('Доставлен'), ('Отменён');

INSERT INTO Sizes (SizeName) VALUES ('XS'), ('S'), ('M'), ('L'), ('XL');

INSERT INTO Categories (CategoryName, Description) VALUES
('Верхняя одежда', 'Куртки, пальто, бомберы'),
('Футболки и топы', 'Футболки, лонгсливы, топы'),
('Брюки и джинсы', 'Брюки, джинсы, шорты'),
('Аксессуары',     'Сумки, шапки, шарфы');

INSERT INTO Products (CategoryId, ProductName, Price, Description, IsActive) VALUES
(1, 'Бомбер «Voina»',       28500.00, 'Бомбер из плотной ткани с принтом коллекции. Застёжка на молнию, два боковых кармана, манжеты на резинке.', 1),
(1, 'Куртка «Piter»',       35000.00, 'Куртка свободного кроя с капюшоном. Скрытые карманы, утеплитель. Материал: 100% нейлон.', 1),
(1, 'Пальто «Winter»',      52000.00, 'Длинное пальто из шерстяного драпа. Цвет: тёмно-синий. Двубортная застёжка.', 1),
(2, 'Футболка «Logo»',       8500.00, 'Базовая футболка с вышитым логотипом бренда. Оверсайз силуэт. Материал: 100% хлопок.', 1),
(2, 'Лонгслив «Script»',    12000.00, 'Лонгслив с принтом кириллического шрифта. Материал: 90% хлопок, 10% эластан.', 1),
(2, 'Футболка «Cyrillic»',   9500.00, 'Футболка с кириллическим принтом. Оверсайз. Материал: 100% хлопок.', 1),
(3, 'Брюки «Track»',        18000.00, 'Спортивные брюки с лампасами. Свободный крой, два кармана, регулируемый пояс.', 1),
(3, 'Джинсы «Soviet»',      22000.00, 'Широкие джинсы с необработанным краем. Дистрессинг. Материал: 100% деним.', 1),
(4, 'Шапка «Cyrillic»',      4500.00, 'Шапка-бини с вышитым кириллическим логотипом. Состав: 100% акрил.', 1),
(4, 'Сумка «Tote»',          7800.00, 'Холщовая сумка-шопер с принтом. Длинные ручки. Размер: 40×45 см.', 1);

-- Изображения (заглушки, путь = placeholder)
INSERT INTO ProductImages (ProductId, ImagePath, IsMain) VALUES
(1,  'placeholder', 1), (1,  'placeholder', 0),
(2,  'placeholder', 1), (2,  'placeholder', 0),
(3,  'placeholder', 1),
(4,  'placeholder', 1), (4,  'placeholder', 0),
(5,  'placeholder', 1),
(6,  'placeholder', 1),
(7,  'placeholder', 1), (7,  'placeholder', 0),
(8,  'placeholder', 1),
(9,  'placeholder', 1),
(10, 'placeholder', 1);

-- Варианты товаров по размеру с остатками
INSERT INTO ProductSizes (ProductId, SizeId, Quantity) VALUES
(1,2,5),(1,3,8),(1,4,4),(1,5,2),
(2,2,3),(2,3,6),(2,4,5),(2,5,1),
(3,2,2),(3,3,4),(3,4,3),(3,5,1),
(4,1,10),(4,2,15),(4,3,20),(4,4,12),(4,5,8),
(5,1,5),(5,2,8),(5,3,15),(5,4,10),(5,5,6),
(6,1,12),(6,2,18),(6,3,20),(6,4,15),(6,5,10),
(7,2,4),(7,3,7),(7,4,9),(7,5,3),
(8,2,6),(8,3,10),(8,4,8),(8,5,4),
(9,1,20),(9,2,20),(9,3,20),
(10,1,15),(10,2,15),(10,3,15);

-- Тестовый администратор (пароль: Admin123!)
-- Хэш сгенерирован: password_hash('Admin123!', PASSWORD_BCRYPT)
INSERT INTO Users (RoleId, Email, PasswordHash, FullName) VALUES
(1, 'admin@gosha.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор');
-- Примечание: хэш выше соответствует паролю "password" (для демо)
-- При деплое замените через: php -r "echo password_hash('Admin123!', PASSWORD_BCRYPT);"
