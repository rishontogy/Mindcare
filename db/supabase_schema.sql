-- Supabase Schema for MindCare

-- 1. Create a table for public profiles (linked to auth.users)
CREATE TABLE public.profiles (
    id UUID REFERENCES auth.users(id) ON DELETE CASCADE PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Enable RLS
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;

-- 2. Wellness Data
CREATE TABLE public.wellness_data (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE NOT NULL,
    mood TEXT,
    energy INTEGER,
    stress INTEGER,
    sleep INTEGER,
    activities TEXT, -- Can be expanded to JSONB if needed
    journal TEXT,
    created_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE public.wellness_data ENABLE ROW LEVEL SECURITY;

-- 3. Exercises
CREATE TABLE public.exercises (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE NOT NULL,
    title TEXT NOT NULL,
    category TEXT,
    duration TEXT,
    completed_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE public.exercises ENABLE ROW LEVEL SECURITY;

-- 4. Emergency Contacts
CREATE TABLE public.emergency_contacts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE NOT NULL,
    name TEXT NOT NULL,
    phone TEXT NOT NULL,
    relationship TEXT,
    created_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE public.emergency_contacts ENABLE ROW LEVEL SECURITY;

-- 5. Personal Details
CREATE TABLE public.personal_details (
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE PRIMARY KEY,
    parent_name TEXT,
    parent_phone TEXT,
    parent_email TEXT,
    guardian_name TEXT,
    guardian_phone TEXT,
    guardian_email TEXT,
    spouse_name TEXT,
    spouse_phone TEXT,
    spouse_email TEXT,
    emergency_contact TEXT,
    emergency_phone TEXT,
    updated_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE public.personal_details ENABLE ROW LEVEL SECURITY;

-- 6. Mood History
CREATE TABLE public.mood_history (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE NOT NULL,
    mood INTEGER NOT NULL CHECK (mood >= 1 AND mood <= 5),
    note TEXT,
    created_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE public.mood_history ENABLE ROW LEVEL SECURITY;

-- 7. Wellness Goals
CREATE TABLE public.wellness_goals (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    category TEXT,
    completed BOOLEAN DEFAULT false,
    created_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE public.wellness_goals ENABLE ROW LEVEL SECURITY;

-- 8. Exercise Reviews
CREATE TABLE public.exercise_reviews (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE NOT NULL,
    exercise_id INTEGER,
    exercise_name TEXT,
    type TEXT,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    feedback TEXT,
    story TEXT,
    created_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE public.exercise_reviews ENABLE ROW LEVEL SECURITY;

-- RLS POLICIES (Users can only see/edit their own data)

-- Profiles
CREATE POLICY "Users can view own profile" ON profiles FOR SELECT USING (auth.uid() = id);
CREATE POLICY "Users can update own profile" ON profiles FOR UPDATE USING (auth.uid() = id);

-- Wellness Data
CREATE POLICY "Users can access own wellness data" ON wellness_data FOR ALL USING (auth.uid() = user_id);

-- Exercises
CREATE POLICY "Users can access own exercises" ON exercises FOR ALL USING (auth.uid() = user_id);

-- Emergency Contacts
CREATE POLICY "Users can access own contacts" ON emergency_contacts FOR ALL USING (auth.uid() = user_id);

-- Personal Details
CREATE POLICY "Users can access own details" ON personal_details FOR ALL USING (auth.uid() = user_id);

-- Mood History
CREATE POLICY "Users can access own mood history" ON mood_history FOR ALL USING (auth.uid() = user_id);

-- Wellness Goals
CREATE POLICY "Users can access own goals" ON wellness_goals FOR ALL USING (auth.uid() = user_id);

-- Exercise Reviews
CREATE POLICY "Users can access own reviews" ON exercise_reviews FOR ALL USING (auth.uid() = user_id);

-- TRIGGER for automatic Profile creation on auth.users signup
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
  INSERT INTO public.profiles (id, name, email)
  VALUES (new.id, new.raw_user_meta_data->>'name', new.email);
  RETURN new;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();
