// Log in and register. The forms send POST /api/login and /api/register; Laravel checks
// the password and keeps the session (app/Http/Controllers/Api/AuthController.php).
// The 422 messages appear next to the fields, the same way as on the item form.
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import Field from '@/components/Field.jsx';
import { useSession } from '@/session.jsx';

function AuthFrame({ title, children, footer }) {
  return (
    <section className="mx-auto max-w-sm space-y-5">
      <h1 className="text-2xl font-bold tracking-tight">{title}</h1>
      <div className="rounded-xl border bg-background p-5">{children}</div>
      <p className="text-sm text-muted-foreground">{footer}</p>
    </section>
  );
}

/** The submit-and-show-422 pattern of ItemForm, for a small form. */
function useAuthForm(initial, submit) {
  const [values, setValues] = useState(initial);
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const change = (e) => setValues((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  async function handleSubmit(e) {
    e.preventDefault();
    setSaving(true);
    try {
      await submit(values);
    } catch (err) {
      setErrors(err.status === 422 ? err.errors
        : { form: [err.status === 429 ? 'Too many tries. Wait a minute and try again.' : err.message] });
      setSaving(false);
    }
  }
  return { values, errors, saving, change, handleSubmit };
}

export function LoginPage() {
  const { login } = useSession();
  const navigate = useNavigate();
  const form = useAuthForm({ email: '', password: '' }, async (values) => {
    await login(values);
    navigate('/');
  });

  return (
    <AuthFrame title="Log in" footer={<>New to the floor? <Link to="/register" className="underline">Register</Link>.</>}>
      <form onSubmit={form.handleSubmit} noValidate className="space-y-4">
        <Field id="email" label="Email" type="email" autoComplete="username" value={form.values.email}
          onChange={form.change} errors={form.errors.email} />
        <Field id="password" label="Password" type="password" autoComplete="current-password"
          value={form.values.password} onChange={form.change} errors={form.errors.password} />
        {form.errors.form && <p role="alert" className="text-sm text-destructive">{form.errors.form[0]}</p>}
        <Button type="submit" className="w-full" disabled={form.saving}>{form.saving ? 'Logging in…' : 'Log in'}</Button>
      </form>
    </AuthFrame>
  );
}

export function RegisterPage() {
  const { register } = useSession();
  const navigate = useNavigate();
  const form = useAuthForm({ name: '', email: '', password: '', password_confirmation: '' }, async (values) => {
    await register(values);
    navigate('/');
  });

  return (
    <AuthFrame title="Register" footer={<>Already registered? <Link to="/login" className="underline">Log in</Link>.</>}>
      <form onSubmit={form.handleSubmit} noValidate className="space-y-4">
        <Field id="name" label="Name" autoComplete="name" value={form.values.name} onChange={form.change} errors={form.errors.name} />
        <Field id="email" label="Email" type="email" autoComplete="email" value={form.values.email}
          onChange={form.change} errors={form.errors.email} />
        <Field id="password" label="Password" type="password" autoComplete="new-password" hint="At least 8 characters."
          value={form.values.password} onChange={form.change} errors={form.errors.password} />
        <Field id="password_confirmation" label="Confirm password" type="password" autoComplete="new-password"
          value={form.values.password_confirmation} onChange={form.change} errors={form.errors.password_confirmation} />
        {form.errors.form && <p role="alert" className="text-sm text-destructive">{form.errors.form[0]}</p>}
        <Button type="submit" className="w-full" disabled={form.saving}>{form.saving ? 'Creating account…' : 'Create account'}</Button>
      </form>
    </AuthFrame>
  );
}
