-- Thêm nhiều từ vựng hơn vào từ điển
USE eduapp;

-- Thêm từ vựng cơ bản (Beginner)
INSERT INTO dictionary (word, phonetic, vietnamese, english_definition, example, part_of_speech, difficulty) VALUES
-- Colors (Màu sắc)
('red', '/red/', 'đỏ', 'A color like that of blood', 'The apple is red.', 'adjective', 'beginner'),
('blue', '/bluː/', 'xanh dương', 'A color like that of the sky', 'The sky is blue.', 'adjective', 'beginner'),
('green', '/ɡriːn/', 'xanh lá', 'A color like that of grass', 'The grass is green.', 'adjective', 'beginner'),
('yellow', '/ˈjeloʊ/', 'vàng', 'A color like that of the sun', 'The sun is yellow.', 'adjective', 'beginner'),
('black', '/blæk/', 'đen', 'A color like that of coal', 'The night is black.', 'adjective', 'beginner'),
('white', '/waɪt/', 'trắng', 'A color like that of snow', 'The snow is white.', 'adjective', 'beginner'),

-- Numbers (Số đếm)
('one', '/wʌn/', 'một', 'The number 1', 'I have one apple.', 'noun', 'beginner'),
('two', '/tuː/', 'hai', 'The number 2', 'I have two books.', 'noun', 'beginner'),
('three', '/θriː/', 'ba', 'The number 3', 'I have three cats.', 'noun', 'beginner'),
('four', '/fɔːr/', 'bốn', 'The number 4', 'I have four dogs.', 'noun', 'beginner'),
('five', '/faɪv/', 'năm', 'The number 5', 'I have five fingers.', 'noun', 'beginner'),

-- Family (Gia đình)
('mother', '/ˈmʌðər/', 'mẹ', 'A female parent', 'My mother is kind.', 'noun', 'beginner'),
('father', '/ˈfɑːðər/', 'cha', 'A male parent', 'My father is strong.', 'noun', 'beginner'),
('sister', '/ˈsɪstər/', 'chị/em gái', 'A female sibling', 'My sister is beautiful.', 'noun', 'beginner'),
('brother', '/ˈbrʌðər/', 'anh/em trai', 'A male sibling', 'My brother is tall.', 'noun', 'beginner'),
('family', '/ˈfæmɪli/', 'gia đình', 'A group of people related by blood', 'I love my family.', 'noun', 'beginner'),

-- Food (Thức ăn)
('rice', '/raɪs/', 'cơm', 'A grain used as food', 'I eat rice every day.', 'noun', 'beginner'),
('bread', '/bred/', 'bánh mì', 'A food made from flour', 'I like fresh bread.', 'noun', 'beginner'),
('water', '/ˈwɔːtər/', 'nước', 'A clear liquid for drinking', 'I drink water.', 'noun', 'beginner'),
('milk', '/mɪlk/', 'sữa', 'A white liquid from cows', 'I drink milk.', 'noun', 'beginner'),
('apple', '/ˈæpl/', 'táo', 'A round fruit with red or green skin', 'I eat an apple.', 'noun', 'beginner'),

-- Animals (Động vật)
('dog', '/dɔːɡ/', 'chó', 'A domestic animal that barks', 'I have a dog.', 'noun', 'beginner'),
('cat', '/kæt/', 'mèo', 'A small domestic animal that purrs', 'I have a cat.', 'noun', 'beginner'),
('bird', '/bɜːrd/', 'chim', 'A flying animal with feathers', 'I see a bird.', 'noun', 'beginner'),
('fish', '/fɪʃ/', 'cá', 'An animal that lives in water', 'I see a fish.', 'noun', 'beginner'),
('horse', '/hɔːrs/', 'ngựa', 'A large animal used for riding', 'I ride a horse.', 'noun', 'beginner'),

-- Body parts (Bộ phận cơ thể)
('head', '/hed/', 'đầu', 'The top part of the body', 'I have a head.', 'noun', 'beginner'),
('hand', '/hænd/', 'tay', 'The part of the body at the end of the arm', 'I use my hands.', 'noun', 'beginner'),
('eye', '/aɪ/', 'mắt', 'The organ used for seeing', 'I have two eyes.', 'noun', 'beginner'),
('mouth', '/maʊθ/', 'miệng', 'The opening used for eating and speaking', 'I open my mouth.', 'noun', 'beginner'),
('ear', '/ɪr/', 'tai', 'The organ used for hearing', 'I have two ears.', 'noun', 'beginner'),

-- Common verbs (Động từ thông dụng)
('eat', '/iːt/', 'ăn', 'To consume food', 'I eat breakfast.', 'verb', 'beginner'),
('drink', '/drɪŋk/', 'uống', 'To consume liquid', 'I drink water.', 'verb', 'beginner'),
('sleep', '/sliːp/', 'ngủ', 'To rest with eyes closed', 'I sleep at night.', 'verb', 'beginner'),
('walk', '/wɔːk/', 'đi bộ', 'To move on foot', 'I walk to school.', 'verb', 'beginner'),
('run', '/rʌn/', 'chạy', 'To move quickly on foot', 'I run in the park.', 'verb', 'beginner'),

-- Intermediate level words
-- Emotions (Cảm xúc)
('happy', '/ˈhæpi/', 'vui vẻ', 'Feeling or showing pleasure', 'I am happy today.', 'adjective', 'intermediate'),
('sad', '/sæd/', 'buồn', 'Feeling or showing sorrow', 'I feel sad.', 'adjective', 'intermediate'),
('angry', '/ˈæŋɡri/', 'giận dữ', 'Feeling or showing anger', 'I am angry.', 'adjective', 'intermediate'),
('excited', '/ɪkˈsaɪtɪd/', 'phấn khích', 'Feeling very enthusiastic', 'I am excited about the trip.', 'adjective', 'intermediate'),
('worried', '/ˈwɜːrid/', 'lo lắng', 'Feeling anxious or concerned', 'I am worried about the test.', 'adjective', 'intermediate'),

-- Weather (Thời tiết)
('sunny', '/ˈsʌni/', 'nắng', 'Having bright sunlight', 'It is sunny today.', 'adjective', 'intermediate'),
('rainy', '/ˈreɪni/', 'mưa', 'Having rain', 'It is rainy today.', 'adjective', 'intermediate'),
('cloudy', '/ˈklaʊdi/', 'nhiều mây', 'Having clouds', 'It is cloudy today.', 'adjective', 'intermediate'),
('windy', '/ˈwɪndi/', 'gió', 'Having wind', 'It is windy today.', 'adjective', 'intermediate'),
('cold', '/koʊld/', 'lạnh', 'Having low temperature', 'It is cold today.', 'adjective', 'intermediate'),

-- Jobs (Nghề nghiệp)
('teacher', '/ˈtiːtʃər/', 'giáo viên', 'A person who teaches', 'My mother is a teacher.', 'noun', 'intermediate'),
('doctor', '/ˈdɑːktər/', 'bác sĩ', 'A person who treats sick people', 'My father is a doctor.', 'noun', 'intermediate'),
('engineer', '/ˌendʒɪˈnɪr/', 'kỹ sư', 'A person who designs and builds things', 'My brother is an engineer.', 'noun', 'intermediate'),
('student', '/ˈstuːdnt/', 'học sinh', 'A person who studies', 'I am a student.', 'noun', 'intermediate'),
('worker', '/ˈwɜːrkər/', 'công nhân', 'A person who works', 'My uncle is a worker.', 'noun', 'intermediate'),

-- Places (Địa điểm)
('school', '/skuːl/', 'trường học', 'A place where students learn', 'I go to school.', 'noun', 'intermediate'),
('hospital', '/ˈhɑːspɪtl/', 'bệnh viện', 'A place where sick people are treated', 'I go to the hospital.', 'noun', 'intermediate'),
('library', '/ˈlaɪbreri/', 'thư viện', 'A place where books are kept', 'I study in the library.', 'noun', 'intermediate'),
('restaurant', '/ˈrestrɑːnt/', 'nhà hàng', 'A place where food is served', 'I eat at a restaurant.', 'noun', 'intermediate'),
('market', '/ˈmɑːrkɪt/', 'chợ', 'A place where goods are sold', 'I buy food at the market.', 'noun', 'intermediate'),

-- Advanced level words
-- Academic words (Từ vựng học thuật)
('analyze', '/ˈænəlaɪz/', 'phân tích', 'To examine something in detail', 'I analyze the data.', 'verb', 'advanced'),
('conclude', '/kənˈkluːd/', 'kết luận', 'To reach a decision or opinion', 'I conclude the study.', 'verb', 'advanced'),
('demonstrate', '/ˈdemənstreɪt/', 'chứng minh', 'To show how something works', 'I demonstrate the method.', 'verb', 'advanced'),
('evaluate', '/ɪˈvæljueɪt/', 'đánh giá', 'To judge the quality of something', 'I evaluate the results.', 'verb', 'advanced'),
('implement', '/ˈɪmplɪment/', 'thực hiện', 'To put a plan into action', 'I implement the strategy.', 'verb', 'advanced'),

-- Business words (Từ vựng kinh doanh)
('strategy', '/ˈstrætədʒi/', 'chiến lược', 'A plan to achieve a goal', 'We develop a strategy.', 'noun', 'advanced'),
('innovation', '/ˌɪnəˈveɪʃn/', 'đổi mới', 'A new idea or method', 'We need innovation.', 'noun', 'advanced'),
('efficiency', '/ɪˈfɪʃnsi/', 'hiệu quả', 'The quality of doing something well', 'We improve efficiency.', 'noun', 'advanced'),
('collaboration', '/kəˌlæbəˈreɪʃn/', 'hợp tác', 'Working together with others', 'We work in collaboration.', 'noun', 'advanced'),
('leadership', '/ˈliːdərʃɪp/', 'lãnh đạo', 'The ability to lead others', 'She shows leadership.', 'noun', 'advanced'),

-- Technology words (Từ vựng công nghệ)
('algorithm', '/ˈælɡərɪðəm/', 'thuật toán', 'A set of rules for solving problems', 'I write an algorithm.', 'noun', 'advanced'),
('database', '/ˈdeɪtəbeɪs/', 'cơ sở dữ liệu', 'A collection of organized data', 'I use a database.', 'noun', 'advanced'),
('interface', '/ˈɪntərfeɪs/', 'giao diện', 'A connection between systems', 'I design an interface.', 'noun', 'advanced'),
('protocol', '/ˈproʊtəkɔːl/', 'giao thức', 'A set of rules for communication', 'I follow the protocol.', 'noun', 'advanced'),
('encryption', '/ɪnˈkrɪpʃn/', 'mã hóa', 'Converting data into code', 'I use encryption.', 'noun', 'advanced'),

-- Medical words (Từ vựng y tế)
('diagnosis', '/ˌdaɪəɡˈnoʊsɪs/', 'chẩn đoán', 'Identifying a disease', 'The doctor makes a diagnosis.', 'noun', 'advanced'),
('symptom', '/ˈsɪmptəm/', 'triệu chứng', 'A sign of illness', 'I have symptoms.', 'noun', 'advanced'),
('treatment', '/ˈtriːtmənt/', 'điều trị', 'Medical care for illness', 'I receive treatment.', 'noun', 'advanced'),
('prescription', '/prɪˈskrɪpʃn/', 'đơn thuốc', 'A written order for medicine', 'I get a prescription.', 'noun', 'advanced'),
('recovery', '/rɪˈkʌvəri/', 'hồi phục', 'Returning to health', 'I make a recovery.', 'noun', 'advanced'),

-- Environmental words (Từ vựng môi trường)
('sustainability', '/səˌsteɪnəˈbɪləti/', 'bền vững', 'Using resources without depleting them', 'We promote sustainability.', 'noun', 'advanced'),
('biodiversity', '/ˌbaɪoʊdaɪˈvɜːrsəti/', 'đa dạng sinh học', 'Variety of life forms', 'We protect biodiversity.', 'noun', 'advanced'),
('renewable', '/rɪˈnuːəbl/', 'tái tạo', 'Able to be replaced naturally', 'We use renewable energy.', 'adjective', 'advanced'),
('pollution', '/pəˈluːʃn/', 'ô nhiễm', 'Contamination of the environment', 'We reduce pollution.', 'noun', 'advanced'),
('conservation', '/ˌkɑːnsərˈveɪʃn/', 'bảo tồn', 'Protection of natural resources', 'We support conservation.', 'noun', 'advanced'),

-- Phrasal verbs (Cụm động từ)
('look up', '/lʊk ʌp/', 'tra cứu', 'To search for information', 'I look up the word.', 'verb', 'intermediate'),
('give up', '/ɡɪv ʌp/', 'từ bỏ', 'To stop trying', 'I give up smoking.', 'verb', 'intermediate'),
('put off', '/pʊt ɔːf/', 'trì hoãn', 'To postpone', 'I put off the meeting.', 'verb', 'intermediate'),
('get along', '/ɡet əˈlɔːŋ/', 'hòa thuận', 'To have a good relationship', 'We get along well.', 'verb', 'intermediate'),
('come up with', '/kʌm ʌp wɪð/', 'nghĩ ra', 'To think of an idea', 'I come up with a solution.', 'verb', 'intermediate'),

-- Idioms (Thành ngữ)
('break the ice', '/breɪk ðə aɪs/', 'phá vỡ sự im lặng', 'To start a conversation', 'I break the ice at the party.', 'idiom', 'advanced'),
('hit the nail on the head', '/hɪt ðə neɪl ɑːn ðə hed/', 'đúng trọng tâm', 'To be exactly right', 'You hit the nail on the head.', 'idiom', 'advanced'),
('piece of cake', '/piːs əv keɪk/', 'dễ như ăn bánh', 'Something very easy', 'The test was a piece of cake.', 'idiom', 'advanced'),
('cost an arm and a leg', '/kɔːst ən ɑːrm ənd ə leɡ/', 'rất đắt', 'To be very expensive', 'The car costs an arm and a leg.', 'idiom', 'advanced'),
('let the cat out of the bag', '/let ðə kæt aʊt əv ðə bæɡ/', 'tiết lộ bí mật', 'To reveal a secret', 'I let the cat out of the bag.', 'idiom', 'advanced');

-- Thêm từ vựng theo chủ đề
-- Technology (Công nghệ)
INSERT INTO dictionary (word, phonetic, vietnamese, english_definition, example, part_of_speech, difficulty) VALUES
('computer', '/kəmˈpjuːtər/', 'máy tính', 'An electronic device for processing data', 'I use a computer.', 'noun', 'intermediate'),
('internet', '/ˈɪntərnet/', 'internet', 'A global network of computers', 'I browse the internet.', 'noun', 'intermediate'),
('software', '/ˈsɔːftwer/', 'phần mềm', 'Computer programs and applications', 'I install software.', 'noun', 'intermediate'),
('hardware', '/ˈhɑːrdwer/', 'phần cứng', 'Physical computer components', 'I upgrade hardware.', 'noun', 'intermediate'),
('website', '/ˈwebsaɪt/', 'trang web', 'A collection of web pages', 'I visit a website.', 'noun', 'intermediate'),

-- Education (Giáo dục)
('university', '/ˌjuːnɪˈvɜːrsəti/', 'đại học', 'An institution of higher education', 'I study at university.', 'noun', 'intermediate'),
('professor', '/prəˈfesər/', 'giáo sư', 'A senior academic teacher', 'My professor is knowledgeable.', 'noun', 'intermediate'),
('research', '/rɪˈsɜːrtʃ/', 'nghiên cứu', 'Systematic investigation', 'I conduct research.', 'noun', 'intermediate'),
('thesis', '/ˈθiːsɪs/', 'luận văn', 'A long academic paper', 'I write a thesis.', 'noun', 'advanced'),
('scholarship', '/ˈskɑːlərʃɪp/', 'học bổng', 'Financial aid for students', 'I receive a scholarship.', 'noun', 'intermediate'),

-- Sports (Thể thao)
('football', '/ˈfʊtbɔːl/', 'bóng đá', 'A team sport with a ball', 'I play football.', 'noun', 'beginner'),
('basketball', '/ˈbæskɪtbɔːl/', 'bóng rổ', 'A team sport with a hoop', 'I play basketball.', 'noun', 'intermediate'),
('tennis', '/ˈtenɪs/', 'quần vợt', 'A racket sport', 'I play tennis.', 'noun', 'intermediate'),
('swimming', '/ˈswɪmɪŋ/', 'bơi lội', 'Moving through water', 'I go swimming.', 'noun', 'intermediate'),
('running', '/ˈrʌnɪŋ/', 'chạy bộ', 'Moving quickly on foot', 'I go running.', 'noun', 'intermediate'),

-- Music (Âm nhạc)
('guitar', '/ɡɪˈtɑːr/', 'guitar', 'A stringed musical instrument', 'I play guitar.', 'noun', 'intermediate'),
('piano', '/piˈænoʊ/', 'piano', 'A keyboard musical instrument', 'I play piano.', 'noun', 'intermediate'),
('concert', '/ˈkɑːnsərt/', 'buổi hòa nhạc', 'A musical performance', 'I attend a concert.', 'noun', 'intermediate'),
('melody', '/ˈmelədi/', 'giai điệu', 'A sequence of musical notes', 'I hum a melody.', 'noun', 'advanced'),
('rhythm', '/ˈrɪðəm/', 'nhịp điệu', 'A regular pattern of sound', 'I feel the rhythm.', 'noun', 'advanced'),

-- Travel (Du lịch)
('passport', '/ˈpæspɔːrt/', 'hộ chiếu', 'A travel document', 'I need a passport.', 'noun', 'intermediate'),
('visa', '/ˈviːzə/', 'thị thực', 'Permission to enter a country', 'I apply for a visa.', 'noun', 'intermediate'),
('hotel', '/hoʊˈtel/', 'khách sạn', 'A place to stay while traveling', 'I stay at a hotel.', 'noun', 'intermediate'),
('tourist', '/ˈtʊrɪst/', 'du khách', 'A person who travels for pleasure', 'I am a tourist.', 'noun', 'intermediate'),
('souvenir', '/ˌsuːvəˈnɪr/', 'quà lưu niệm', 'A keepsake from travel', 'I buy a souvenir.', 'noun', 'intermediate');
